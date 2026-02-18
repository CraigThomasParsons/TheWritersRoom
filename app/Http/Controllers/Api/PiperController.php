<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Epic;
use App\Models\EpicStatus;
use App\Models\Persona;
use App\Models\PiperProjectAnalysis;
use App\Models\Project;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\StoryStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Handles Piper machine endpoints for project-context ingestion and draft generation.
 *
 * Responsibilities:
 * - Provide project input context for the analysis stage
 * - Persist expanded analysis signals (not summaries)
 * - Materialize epics and stories from structured Piper output
 */
final class PiperController extends Controller
{
    /**
     * Return WritersRoom project context plus related conversation material.
     */
    public function projectInput(Request $request, int $projectId): JsonResponse
    {
        // Enforce internal token auth for machine-to-machine access.
        $this->authorizeRequest($request);

        $project = Project::find($projectId);

        if (! $project) {
            return response()->json([
                'status' => 'error',
                'message' => "Project {$projectId} not found in WritersRoom.",
            ], 404);
        }

        // Default to no error so callers can distinguish explicit failure paths.
        $conversationError = null;

        // Initialize to empty collection so response shape remains stable.
        $conversations = collect();

        try {
            // Pull source conversations from ChatProjects for upstream context expansion.
            $conversations = DB::connection('chatprojects')
                ->table('conversations')
                ->where('project_id', $project->id)
                ->orderByDesc('updated_at')
                ->select([
                    'id',
                    'title',
                    'share_url',
                    'source_type',
                    'raw_content',
                    'updated_at',
                ])
                ->get();
        } catch (\Throwable $exception) {
            // Preserve request success with partial data when cross-service reads fail.
            $conversationError = 'Could not load conversations from ChatProjects.';
        }

        // Include recent analyses so Piper can choose incrementally instead of redoing work.
        $latestAnalyses = $project->piperAnalyses()
            ->limit(10)
            ->get();

        // Return normalized project input payload for deterministic Piper consumption.
        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'code_context' => [
                    'code_folder' => $project->code_folder,
                    'local_location' => $project->local_location,
                    'github_repo' => $project->github_repo,
                    'gitea_location' => $project->gitea_location,
                    'framework_description' => $project->framework_description,
                    'languages' => $project->languages,
                ],
            ],
            'conversations' => $conversations,
            'conversation_error' => $conversationError,
            'latest_analyses' => $latestAnalyses,
            // Provide current active personas so Piper can map stories consistently.
            'available_personas' => Persona::query()->active()->orderBy('name')->get(['id', 'key', 'name']),
        ]);
    }

    /**
     * Persist expanded analysis signals extracted by Piper.
     */
    public function storeAnalysis(Request $request, int $projectId): JsonResponse
    {
        // Require valid service token before accepting analysis writes.
        $this->authorizeRequest($request);

        // Auto-create the project record if it doesn't exist yet.
        $project = Project::firstOrCreate(
            ['id' => $projectId],
            ['name' => "Project {$projectId}"]
        );

        // Validate fully structured analysis payload.
        $validated = $request->validate([
            'source_conversation_ids' => ['nullable', 'array'],
            'source_conversation_ids.*' => ['integer'],
            'recurring_themes' => ['nullable', 'array'],
            'recurring_themes.*' => ['string'],
            'repeated_goals' => ['nullable', 'array'],
            'repeated_goals.*' => ['string'],
            'phases_mentioned' => ['nullable', 'array'],
            'phases_mentioned.*' => ['string'],
            'failure_modes_discussed' => ['nullable', 'array'],
            'failure_modes_discussed.*' => ['string'],
            'technical_constraints' => ['nullable', 'array'],
            'technical_constraints.*' => ['string'],
            'model_routing_decisions' => ['nullable', 'array'],
            'expanded_context' => ['nullable', 'string'],
            'raw_payload' => ['nullable', 'array'],
        ]);

        // Store analysis as an immutable event record that can be replayed later.
        $analysis = PiperProjectAnalysis::create([
            'project_id' => $project->id,
            ...$validated,
        ]);

        // Return created identifier so Piper can chain to build step.
        return response()->json([
            'status' => 'ok',
            'analysis_id' => $analysis->id,
        ], 201);
    }

    /**
     * Materialize epics and stories from structured Piper output.
     */
    public function buildEpicsAndStories(Request $request, int $projectId): JsonResponse
    {
        // Require valid service token before mutating epics/stories.
        $this->authorizeRequest($request);

        // Auto-create the project record if it doesn't exist yet.
        $project = Project::firstOrCreate(
            ['id' => $projectId],
            ['name' => "Project {$projectId}"]
        );

        // Validate strict nested structure to keep generation deterministic.
        $validated = $request->validate([
            'analysis_id' => ['nullable', 'integer', 'exists:piper_project_analyses,id'],
            'epics' => ['required', 'array', 'min:1'],
            'epics.*.title' => ['required', 'string', 'max:255'],
            'epics.*.summary' => ['nullable', 'string'],
            'epics.*.stories' => ['required', 'array', 'min:1'],
            'epics.*.stories.*.title' => ['required', 'string', 'max:255'],
            'epics.*.stories.*.narrative' => ['required', 'string'],
            'epics.*.stories.*.acceptance_criteria' => ['nullable', 'string'],
            'epics.*.stories.*.persona_key' => ['nullable', 'string', 'max:255'],
            'epics.*.stories.*.persona_name' => ['nullable', 'string', 'max:255'],
            'epics.*.stories.*.priority' => ['nullable', 'integer'],
            'epics.*.stories.*.est_points' => ['nullable', 'integer'],
            'epics.*.stories.*.status_key' => ['nullable', 'string', 'max:64'],
        ]);

        // Resolve default statuses up front to avoid partial writes later.
        $activeEpicStatus = EpicStatus::byKey('active') ?? EpicStatus::query()->first();
        $defaultStoryStatus = StoryStatus::byKey('draft') ?? StoryStatus::query()->first();

        // Guard clause: fail fast when baseline statuses are unavailable.
        if (! $activeEpicStatus || ! $defaultStoryStatus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Required status records are missing. Seed statuses first.',
            ], 422);
        }

        // Track creation/update totals for transparent automation reporting.
        $createdEpics = 0;
        $updatedEpics = 0;
        $createdStories = 0;
        $updatedStories = 0;
        $storyRecords = [];

        // Process each generated epic payload independently.
        foreach ($validated['epics'] as $epicPayload) {
            // Upsert epic by project+title so repeated runs remain idempotent.
            $epic = Epic::updateOrCreate(
                [
                    'chat_project_id' => $project->id,
                    'title' => $epicPayload['title'],
                ],
                [
                    'summary' => $epicPayload['summary'] ?? null,
                    'epic_status_id' => $activeEpicStatus->id,
                ]
            );

            // Update counters for traceability.
            if ($epic->wasRecentlyCreated) {
                $createdEpics++;
            } else {
                $updatedEpics++;
            }

            // Process each story under the current epic.
            foreach ($epicPayload['stories'] as $storyPayload) {
                // Initialize persona linkage as nullable by design.
                $personaId = null;

                // Resolve or create persona by key when one is provided.
                if (! empty($storyPayload['persona_key'])) {
                    $persona = Persona::firstOrCreate(
                        ['key' => $storyPayload['persona_key']],
                        [
                            'name' => $storyPayload['persona_name'] ?? ucfirst(str_replace('_', ' ', $storyPayload['persona_key'])),
                            'summary' => null,
                            'details' => null,
                            'is_active' => true,
                        ]
                    );

                    // Store resolved persona id for story linkage.
                    $personaId = $persona->id;
                }

                // Default story status to draft unless a valid override is provided.
                $storyStatus = $defaultStoryStatus;

                // Use requested status when it maps to a known local key.
                if (! empty($storyPayload['status_key'])) {
                    $storyStatus = StoryStatus::byKey($storyPayload['status_key']) ?? $defaultStoryStatus;
                }

                // Upsert story by epic+title to keep reruns safe.
                $story = Story::updateOrCreate(
                    [
                        'epic_id' => $epic->id,
                        'title' => $storyPayload['title'],
                    ],
                    [
                        'narrative' => $storyPayload['narrative'],
                        'acceptance_criteria' => $storyPayload['acceptance_criteria'] ?? null,
                        'persona_id' => $personaId,
                        'story_status_id' => $storyStatus->id,
                        'priority' => $storyPayload['priority'] ?? 0,
                        'est_points' => $storyPayload['est_points'] ?? null,
                    ]
                );

                // Update counters for downstream monitoring.
                if ($story->wasRecentlyCreated) {
                    $createdStories++;
                } else {
                    $updatedStories++;
                }

                // Track story identifiers so Piper can attach follow-up comments.
                $storyRecords[] = [
                    'story_id' => $story->id,
                    'story_title' => $story->title,
                    'epic_id' => $epic->id,
                    'epic_title' => $epic->title,
                    'was_created' => $story->wasRecentlyCreated,
                ];
            }
        }

        // Return summary metrics for Piper run logs and observability.
        return response()->json([
            'status' => 'ok',
            'project_id' => $project->id,
            'summary' => [
                'epics_created' => $createdEpics,
                'epics_updated' => $updatedEpics,
                'stories_created' => $createdStories,
                'stories_updated' => $updatedStories,
            ],
            'story_records' => $storyRecords,
        ], 201);
    }

    /**
     * Store a Piper-authored comment on a story ticket.
     */
    public function storeStoryComment(Request $request, int $storyId): JsonResponse
    {
        // Require valid service token before accepting Piper comments.
        $this->authorizeRequest($request);

        // Guard against comments on unknown stories.
        $story = Story::findOrFail($storyId);

        // Require a message to prevent empty comments from automation.
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        // Record Piper's comment with consistent attribution.
        $comment = StoryComment::create([
            'story_id' => $story->id,
            'author_name' => 'Piper',
            'author_type' => 'piper',
            'message' => $validated['message'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'status' => 'ok',
            'comment_id' => $comment->id,
        ], 201);
    }

    /**
     * Validate Piper token from either Bearer auth or custom header.
     */
    private function authorizeRequest(Request $request): void
    {
        // Read configured token from services config.
        $token = config('services.piper.token');

        // Guard clause: reject when token config is missing.
        if (empty($token)) {
            abort(500, 'Piper token not configured.');
        }

        // Support both bearer and explicit header formats.
        $provided = $request->bearerToken()
            ?? $request->header('X-Piper-Token');

        // Guard clause: reject invalid credentials immediately.
        if ($provided !== $token) {
            abort(403, 'Invalid token.');
        }
    }
}
