<?php

namespace App\Http\Controllers;

use App\Models\Epic;
use App\Models\EpicStatus;
use App\Models\Project;
use App\Models\StoryStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * EpicController handles CRUD operations for epics.
 *
 * Epics group related stories together and can be associated
 * with a project (synced locally from ChatProjects API).
 */
class EpicController extends Controller
{
    /**
     * Display a listing of epics.
     *
     * Supports filtering by search term, status, and project.
     */
    public function index(Request $request)
    {
        // Build query with eager-loaded relationships
        $query = Epic::with(['status', 'project']);

        // Filter by search term across title and summary
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('summary', 'like', "%{$request->search}%");
            });
        }

        // Filter by epic status key
        if ($request->has('status') && $request->status) {
            $query->whereHas('status', fn ($q) => $q->where('key', $request->status));
        }

        // Filter by project from ChatProjects
        if ($request->has('project_id') && $request->project_id) {
            $query->where('chat_project_id', $request->project_id);
        }

        // Paginate results with story count
        $epics = $query->withCount('stories')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Load filter options
        $statuses = EpicStatus::all();
        $projects = $this->getProjects();

        return view('epics.index', compact('epics', 'statuses', 'projects'));
    }

    /**
     * Show the form for creating a new epic.
     *
     * Loads statuses and projects for the dropdown selects.
     */
    public function create()
    {
        $statuses = EpicStatus::all();
        $projects = $this->getProjects();

        return view('epics.create', compact('statuses', 'projects'));
    }

    /**
     * Store a newly created epic in the database.
     */
    public function store(Request $request)
    {
        // Validate input including optional project association
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'epic_status_id' => 'required|exists:epic_statuses,id',
            'chat_project_id' => 'nullable|integer',
        ]);

        Epic::create($validated);

        return redirect()->route('epics.index')
            ->with('success', 'Epic created successfully.');
    }

    public function show(Epic $epic)
    {
        // Eager load relationships for display
        $epic->load(['status', 'project', 'stories' => function ($query) {
            $query->with(['status', 'persona'])->orderBy('priority', 'desc');
        }]);

        return view('epics.show', compact('epic'));
    }

    /**
     * Show the form for editing an epic.
     */
    public function edit(Epic $epic)
    {
        $statuses = EpicStatus::all();
        $projects = $this->getProjects();

        return view('epics.edit', compact('epic', 'statuses', 'projects'));
    }

    public function update(Request $request, Epic $epic)
    {
        // Validate input including optional project association
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'epic_status_id' => 'required|exists:epic_statuses,id',
            'chat_project_id' => 'nullable|integer',
        ]);

        $epic->update($validated);

        return redirect()->route('epics.index')
            ->with('success', 'Epic updated successfully.');
    }

    public function destroy(Epic $epic)
    {
        $epic->delete();

        return redirect()->route('epics.index')
            ->with('success', 'Epic deleted successfully.');
    }

    public function readyForDev(Request $request, Epic $epic)
    {
        // Guard clause: Ready status must exist for downstream sync to work.
        $readyStatus = StoryStatus::byKey('ready');
        if (! $readyStatus) {
            return back()->with('error', 'Ready status not found. Seed statuses first.');
        }

        // Guard clause: Cannot sync an epic with no stories.
        $storyIds = $epic->stories()->pluck('stories.id');
        if ($storyIds->isEmpty()) {
            return back()->with('error', 'Epic has no stories to mark as ready.');
        }

        // Guard clause: chat project id is required for cross-app sync.
        if (! $epic->chat_project_id) {
            return back()->with('error', 'Epic is missing a project id for DevBacklog sync.');
        }

        // Only update stories that are not already ready to reduce churn.
        $updatedCount = $epic->stories()
            ->where('story_status_id', '!=', $readyStatus->id)
            ->update(['story_status_id' => $readyStatus->id]);

        // Trigger DevBacklog sync after readiness is ensured.
        $syncResult = $this->syncEpicStoriesToDevBacklog($epic->chat_project_id);

        if (! $syncResult['ok']) {
            return back()->with('error', $syncResult['message']);
        }

        return back()->with('success', "Marked {$updatedCount} story(ies) ready and synced to DevBacklog.");
    }

    protected function syncEpicStoriesToDevBacklog(?int $projectId): array
    {
        $baseUrl = config('services.devbacklog.base_url');
        $token = config('services.devbacklog.token');
        $timeoutSeconds = (int) config('services.devbacklog.timeout_seconds', 15);

        // Guard clause: missing config should be surfaced explicitly.
        if (! $baseUrl) {
            return ['ok' => false, 'message' => 'DevBacklog base URL is not configured.'];
        }

        // Guard clause: refuse to sync without auth token.
        if (! $token) {
            return ['ok' => false, 'message' => 'DevBacklog sync token is not configured.'];
        }

        $payload = [];
        if ($projectId) {
            $payload['project_id'] = $projectId;
        }

        try {
            // Use a focused sync endpoint so DevBacklog can pull ready stories for a project.
            $response = Http::timeout($timeoutSeconds)
                ->withToken($token)
                ->post(rtrim($baseUrl, '/') . '/api/stories/sync-ready', $payload);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'DevBacklog sync failed: ' . $response->body(),
                ];
            }

            return ['ok' => true, 'message' => 'DevBacklog sync completed.'];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'DevBacklog sync error: ' . $exception->getMessage(),
            ];
        }
    }

    /**
     * Get all projects from the local database (synced from ChatProjects API).
     *
     * These are synced via `php artisan ccdf:sync-projects`.
     */
    protected function getProjects()
    {
        return Project::orderBy('name')->get();
    }
}
