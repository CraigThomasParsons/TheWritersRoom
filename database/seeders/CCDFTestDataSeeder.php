<?php

namespace Database\Seeders;

use App\Models\Epic;
use App\Models\EpicStatus;
use App\Models\Persona;
use App\Models\Story;
use App\Models\StoryStatus;
use Illuminate\Database\Seeder;

/**
 * Seeds test data for CCDF integration testing
 * 
 * Creates:
 * - 1 Epic: "CCDF Infrastructure"  
 * - 1 Story: "Add provider health check endpoint to Mason"
 * - 1 Persona: "System Administrator"
 */
class CCDFTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create statuses
        $epicActiveStatus = EpicStatus::where('key', 'active')->first();
        $storyReadyStatus = StoryStatus::where('key', 'ready')->first();

        if (!$epicActiveStatus || !$storyReadyStatus) {
            $this->command->error('Status tables not seeded. Run StatusSeeder first.');
            return;
        }

        // Create System Administrator persona if not exists
        $persona = Persona::firstOrCreate(
            ['key' => 'system_admin'],
            [
                'name' => 'System Administrator',
                'summary' => 'Operations team member responsible for monitoring and maintaining system health',
                'details' => 'The System Administrator monitors CCDF services, checks provider availability, reviews execution logs, and ensures smooth operation of the automated pipeline.',
                'is_active' => true,
            ]
        );

        $this->command->info("Persona: {$persona->name} (ID: {$persona->id})");

        // Create CCDF Infrastructure epic linked to project #2
        $epic = Epic::firstOrCreate(
            [
                'title' => 'CCDF Infrastructure',
                'chat_project_id' => 2,
            ],
            [
                'summary' => 'Core infrastructure components for the Context Controlled Development Factory. Includes agent scaffolding, event bus, API endpoints, and inter-service communication.',
                'epic_status_id' => $epicActiveStatus->id,
            ]
        );

        $this->command->info("Epic: {$epic->title} (ID: {$epic->id})");

        // Create the test story from the test plan
        $story = Story::firstOrCreate(
            [
                'title' => 'Add provider health check endpoint to Mason',
                'epic_id' => $epic->id,
            ],
            [
                'narrative' => 'As a System Administrator, I want to check the health status of all configured providers so that I can monitor which providers are available.',
                'acceptance_criteria' => <<<'AC'
## Acceptance Criteria

1. **Health Endpoint**
   - GET `/health` returns JSON with provider status
   - Response format: `{ "status": "ok|degraded|down", "providers": [...], "timestamp": "ISO8601" }`

2. **Provider Status**
   - Each provider shows: `name`, `available` (bool), `last_success`, `last_failure`
   - Providers are checked in parallel for speed

3. **Overall Health**
   - Response includes overall system health score (0-100)
   - `status: "ok"` when all providers available
   - `status: "degraded"` when some providers down
   - `status: "down"` when no providers available

4. **Performance**
   - Endpoint responds within 500ms
   - Provider checks timeout after 2 seconds each

5. **Error Handling**
   - Failed provider checks don't crash the endpoint
   - Errors are logged but endpoint still returns valid JSON
AC,
                'persona_id' => $persona->id,
                'story_status_id' => $storyReadyStatus->id,
                'priority' => 100,  // High priority
                'est_points' => 3,
            ]
        );

        $this->command->info("Story: {$story->title} (ID: {$story->id})");
        $this->command->info("Story Status: {$story->status->name}");
        
        $this->command->newLine();
        $this->command->info('✅ CCDF test data seeded successfully!');
        $this->command->info("   Epic #{$epic->id} linked to Project #2 (CCDF)");
        $this->command->info("   Story #{$story->id} is READY for development");
    }
}
