<?php

namespace Database\Seeders;

use App\Models\ProjectRepository;
use Illuminate\Database\Seeder;

class CCDFRepositoriesSeeder extends Seeder
{
    /**
     * Seed the CCDF project repositories.
     * Project ID 2 = "Factory Workbench - Auto Pipeline" (CCDF)
     */
    public function run(): void
    {
        $projectId = 2; // CCDF project
        $basePath = '/home/craigpar/Code';

        $repositories = [
            [
                'name' => 'ContextControlledDevelopmentFactory',
                'path' => "{$basePath}/ContextControlledDevelopmentFactory",
                'type' => 'docs',
                'role' => 'docs',
                'display_order' => 0,
                'is_primary' => true,
            ],
            [
                'name' => 'ChatProjects',
                'path' => "{$basePath}/ChatProjects",
                'type' => 'laravel',
                'role' => 'projects',
                'display_order' => 1,
                'is_primary' => false,
            ],
            [
                'name' => 'TheWritersRoom',
                'path' => "{$basePath}/TheWritersRoom",
                'type' => 'laravel',
                'role' => 'writersroom',
                'display_order' => 2,
                'is_primary' => false,
            ],
            [
                'name' => 'TheDevBacklog',
                'path' => "{$basePath}/TheDevBacklog",
                'type' => 'laravel',
                'role' => 'devbacklog',
                'display_order' => 3,
                'is_primary' => false,
            ],
            [
                'name' => 'TheQAQueue',
                'path' => "{$basePath}/TheQAQueue",
                'type' => 'laravel',
                'role' => 'qaqueue',
                'display_order' => 4,
                'is_primary' => false,
            ],
            [
                'name' => 'Piper',
                'path' => "{$basePath}/Piper",
                'type' => 'python',
                'role' => 'agent',
                'display_order' => 5,
                'is_primary' => false,
            ],
            [
                'name' => 'Mason',
                'path' => "{$basePath}/Mason",
                'type' => 'python',
                'role' => 'agent',
                'display_order' => 6,
                'is_primary' => false,
            ],
            [
                'name' => 'Vera',
                'path' => "{$basePath}/Vera",
                'type' => 'python',
                'role' => 'agent',
                'display_order' => 7,
                'is_primary' => false,
            ],
        ];

        foreach ($repositories as $repo) {
            ProjectRepository::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'path' => $repo['path'],
                ],
                array_merge($repo, ['project_id' => $projectId])
            );
        }

        $this->command->info("Seeded " . count($repositories) . " repositories for CCDF project");
    }
}
