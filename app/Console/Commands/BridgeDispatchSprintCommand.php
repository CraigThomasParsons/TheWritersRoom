<?php

namespace App\Console\Commands;

use App\Models\Sprint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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

        if (!$sprint) {
            $this->error("Sprint {$sprintId} not found.");
            return 1;
        }

        if (strtolower($sprint->status->name ?? '') !== 'ready') {
            $this->warn("Sprint {$sprintId} is not in 'ready' status. Dispatch aborted to prevent premature execution.");
            return 0;
        }

        $this->info("Dispatching Sprint {$sprintId} ({$sprint->title}) to CCDF...");

        // Construct the expected ISBD JSON payload structure
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
