<?php

namespace App\Console\Commands;

use App\Models\ChatProject;
use App\Models\Project;
use Illuminate\Console\Command;

class SyncProjectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ccdf:sync-projects 
                            {--project= : Sync a specific project by ID}
                            {--all : Sync all projects}';

    /**
     * The console command description.
     */
    protected $description = 'Sync projects from ChatProjects to local projects table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectId = $this->option('project');
        $syncAll = $this->option('all');

        if (!$projectId && !$syncAll) {
            $this->error('Please specify --project=ID or --all');
            return self::FAILURE;
        }

        $this->info('Syncing projects from ChatProjects...');

        try {
            if ($projectId) {
                $this->syncProject($projectId);
            } else {
                $this->syncAllProjects();
            }

            $this->info('✓ Project sync completed');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to sync: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Sync a single project by ID.
     */
    protected function syncProject(int $projectId): void
    {
        $chatProject = ChatProject::findOrFail($projectId);
        
        $project = Project::syncFromChatProjects([
            'id' => $chatProject->id,
            'name' => $chatProject->name,
            'code_folder' => $chatProject->code_folder,
            'description' => $chatProject->description ?? null,
        ]);

        $this->line("  Synced: {$project->name} (ID: {$project->id})");
    }

    /**
     * Sync all projects from ChatProjects.
     */
    protected function syncAllProjects(): void
    {
        $chatProjects = ChatProject::all();

        $this->withProgressBar($chatProjects, function ($chatProject) {
            Project::syncFromChatProjects([
                'id' => $chatProject->id,
                'name' => $chatProject->name,
                'code_folder' => $chatProject->code_folder,
                'description' => $chatProject->description ?? null,
            ]);
        });

        $this->newLine();
        $this->line("  Synced {$chatProjects->count()} projects");
    }
}
