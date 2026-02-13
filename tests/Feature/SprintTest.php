<?php

namespace Tests\Feature;

use App\Events\SprintCreated;
use App\Events\SprintReady;
use App\Models\Sprint;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_sprint(): void
    {
        Event::fake([SprintCreated::class]);

        $response = $this->postJson('/api/sprints', [
            'title' => 'Sprint 1',
            'goal' => 'Complete authentication',
            'status' => 'draft',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Sprint 1',
                'goal' => 'Complete authentication',
                'status' => 'draft',
            ]);

        Event::assertDispatched(SprintCreated::class);
    }

    public function test_can_list_sprints(): void
    {
        Sprint::factory()->create([
            'title' => 'Sprint 1',
            'goal' => 'First goal',
        ]);

        $response = $this->getJson('/api/sprints');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Sprint 1']);
    }

    public function test_can_show_sprint(): void
    {
        $sprint = Sprint::factory()->create([
            'title' => 'Sprint 1',
            'goal' => 'First goal',
        ]);

        $response = $this->getJson("/api/sprints/{$sprint->id}");

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Sprint 1',
                'goal' => 'First goal',
            ]);
    }

    public function test_can_update_draft_sprint(): void
    {
        $sprint = Sprint::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->putJson("/api/sprints/{$sprint->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Title']);
    }

    public function test_sprint_becomes_immutable_when_ready(): void
    {
        $sprint = Sprint::factory()->create([
            'status' => 'draft',
            'title' => 'Original Title',
        ]);

        $this->putJson("/api/sprints/{$sprint->id}", [
            'status' => 'ready',
        ]);

        $response = $this->putJson("/api/sprints/{$sprint->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseHas('sprints', [
            'id' => $sprint->id,
            'title' => 'Original Title',
        ]);
    }

    public function test_sprint_ready_event_dispatched_when_status_changes_to_ready(): void
    {
        Event::fake([SprintReady::class]);

        $sprint = Sprint::factory()->create([
            'status' => 'draft',
        ]);

        $this->putJson("/api/sprints/{$sprint->id}", [
            'status' => 'ready',
        ]);

        Event::assertDispatched(SprintReady::class);
    }

    public function test_can_delete_sprint(): void
    {
        $sprint = Sprint::factory()->create();

        $response = $this->deleteJson("/api/sprints/{$sprint->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('sprints', ['id' => $sprint->id]);
    }

    public function test_sprint_has_many_stories(): void
    {
        $sprint = Sprint::factory()->create();
        Story::factory()->create(['sprint_id' => $sprint->id]);
        Story::factory()->create(['sprint_id' => $sprint->id]);

        $response = $this->getJson("/api/sprints/{$sprint->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'stories');
    }
}
