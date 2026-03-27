<?php

namespace App\Console\Commands;

use App\Models\Sprint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * BridgeDispatchSprintCommand is responsible for packaging the sprint context into a structured JSON payload and placing it in the ISBD outbox directory.
 * This command is typically triggered when a sprint transitions to 'ready' status, signaling that it's primed for development.
 * The payload includes sprint details, associated stories, and their linked epics, formatted according to the schema expected by Tess/Mason in the CCDF ecosystem.
 * Additionally, it checks for codebase paths tied to the sprint's stories and can trigger an OpenClaw initialization if no codebase exists, ensuring that
 * the development environment is prepared for the incoming sprint context. Finally, it signals the venv python daemon to ping RabbitMQ, activating the delivery route to CCDF immediately.
 */
class BridgeDispatchSprintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bridge:dispatch-sprint {sprint_id}';

    /**
     * The console command description.
     * ISBD = Inception and Story Bridge to Dev. This command is the final step in the sprint orchestration process, packaging the sprint context and signaling the ISBD architecture to trigger delivery.
     * CCDF = ChatGPT to ChatProjects Bridge, the internal name for the ISBD consumer service in the CCDF ecosystem.
     *
     * @var string
     */
    protected $description = 'Dispatches an active sprint to the ISBD outbox so Tess can orchestrate it in CCDF.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sprintId = $this->argument('sprint_id');
        $sprint = Sprint::with(['stories.epic', 'stories.status', 'stories.persona'])->find($sprintId);
        $strStatusName = '';

        if (!$sprint) {
            $this->error("Sprint {$sprintId} not found.");
            return 1;
        }

        if ($sprint->status && $sprint->status->name) {
            $strStatusName = strtolower($sprint->status->name);
        }

        // Only allow dispatching if the sprint is in 'ready' status to prevent premature execution. This ensures that all sprint context is finalized before delivery.
        if ($strStatusName !== 'ready') {
            $this->warn("Sprint {$sprintId} is not in 'ready' status. Dispatch aborted to prevent premature execution.");
            return 0;
        }

        $this->info("Dispatching Sprint {$sprintId} ({$sprint->title}) to CCDF...");

        // Construct the expected Inception and Story Bridge to Dev JSON payload structure
        $codebasesResult = $this->extractUniqueCodebases($sprint);
        
        if ($codebasesResult['requires_openclaw_initialization'] && !empty($codebasesResult['paths'])) {
            $this->info("Missing codebase detected. Dispatching InitializeProjectCodebaseJob async queue...");
            \App\Jobs\InitializeProjectCodebaseJob::dispatch($codebasesResult['paths'][0]);
        }
        
        $payload = [
            'sprint_id' => $sprint->id,
            'title' => $sprint->title,
            'goal' => $sprint->goal,
            'dispatched_at' => now()->toIso8601String(),
            'codebases' => $codebasesResult['paths'],
            'tasks' => $this->formatStoriesAsTasks($sprint)
        ];

        // The ISBD architecture uses an outbox pattern bridged by the ISBD Aggregator script.
        // We write the trigger folder. ThePostalService picks it up, queries context, and drops it to CCDF.
        $outboxDir = config('services.isbd.outbox_path', '/home/craigpar/Code/InceptionAndStoryBridgeToDev/outbox');
        $packageId = "sprint_{$sprint->id}_" . time();
        $packageDir = rtrim($outboxDir, '/') . '/' . $packageId;
        
        if (!File::exists($packageDir)) {
            File::makeDirectory($packageDir, 0755, true);
        }

        $jsonPath = $packageDir . '/sprint_context.json';
        File::put($jsonPath, json_encode($payload, JSON_PRETTY_PRINT));

        // Create the routing tag for The Postal Service
        $letterPath = $packageDir . '/letter.toml';
        $letterContent = "recipient = \"ccdf\"\npackage_id = \"{$packageId}\"\n";
        File::put($letterPath, $letterContent);

        Log::info("ISBD Bridge: Sprint {$sprint->id} payload written to {$packageDir}");
        $this->info("Payload successfully packaged into {$packageDir}");

        // Ping RabbitMQ via the venv python daemon to activate the delivery route instantly
        $pythonExecutable = '/home/craigpar/Code/ChatGptToChatProjectsBridge/venv/bin/python';
        $scriptPath = '/home/craigpar/Code/InceptionAndStoryBridgeToDev/bin/notify_postal_service.py';
        
        $command = escapeshellarg($pythonExecutable) . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($packageId) . ' 2>&1';
        $output = shell_exec($command);
        
        $this->info("RabbitMQ Signal Output: " . trim($output));

        return 0;
    }

    /**
     * Scrapes all stories in the sprint to find all unique local code folders.
     * If none exist, determines the required path and sets the flag for OpenClaw initialization.
     */
    private function extractUniqueCodebases(Sprint $sprint): array
    {
        $codebases = [];
        $requiresInitialization = false;

        foreach ($sprint->stories as $story) {
            if (isset($story->codeFolders) && is_iterable($story->codeFolders)) {
                foreach ($story->codeFolders as $folder) {
                    $path = $folder->local_path ?? null;
                    if ($path && !in_array($path, $codebases)) {
                        $codebases[] = $path;
                    }
                }
            }
        }

        // If no explicit feature code folders are set on the stories, fall back to the project's root folder.
        // We pluck it from the first story's epic. WR requires every story to belong to an epic, and every epic to a project.
        if (empty($codebases) && $sprint->stories->isNotEmpty()) {
            $firstStory = $sprint->stories->first();
            if ($firstStory->epic && $firstStory->epic->project) {
                 $project = $firstStory->epic->project;
                 if ($project->local_location) {
                     $codebases[] = $project->local_location;
                 } else {
                     // No codebase exists anywhere. Flag for OpenClaw initialization.
                     // TheWritersRoom sometimes prefixes project names (e.g., 'Code: Portfolio'). We want 'Portfolio'.
                     $cleanNameArray = explode(':', $project->name);
                     $baseName = end($cleanNameArray);
                     $sanitizedTitle = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '', $baseName));
                     $codebases[] = '/home/craigpar/Code/' . $sanitizedTitle;
                     $requiresInitialization = true;
                     
                     // Auto-update the project with this new intended location so future stories bind to it
                     $project->local_location = $codebases[0];
                     $project->save();
                 }
            }
        }

        return [
            'paths' => $codebases,
            'requires_openclaw_initialization' => $requiresInitialization
        ];
    }

    /**
     * Formats the WR stories into the task schema expected by Tess/Mason.
     */
    private function formatStoriesAsTasks(Sprint $sprint): array
    {
        $tasks = [];

        foreach ($sprint->stories as $story) {
            $tasks[] = [
                'task_id' => "WR-STORY-{$story->id}",
                'title' => $story->title,
                'description' => $story->narrative,
                'acceptance_criteria' => $story->acceptance_criteria,
                'epic' => $story->epic ? $story->epic->title : 'Unassigned',
                'priority' => $story->priority,
                'status' => $story->status ? $story->status->name : 'Draft'
            ];
        }

        return $tasks;
    }
}
