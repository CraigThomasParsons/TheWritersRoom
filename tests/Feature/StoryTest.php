<?php

namespace Tests\Feature;

use App\Models\Epic;
use App\Models\Sprint;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_story(): void
    {
        $epic = Epic::factory()->create();
        $sprint = Sprint::factory()->create();

        $response = $this->postJson('/api/stories', [
            'title' => 'Login Feature',
            'description' => 'Implement login',
            'epic_id' => $epic->id,
            'sprint_id' => $sprint->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Login Feature',
                'epic_id' => $epic->id,
                'sprint_id' => $sprint->id,
            ]);
    }

    public function test_can_list_stories(): void
    {
        Story::factory()->count(3)->create();

        $response = $this->getJson('/api/stories');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_show_story(): void
    {
        $story = Story::factory()->create([
            'title' => 'Test Story',
        ]);

        $response = $this->getJson("/api/stories/{$story->id}");

        $response->assertStatus(200)
            ->assertJson(['title' => 'Test Story']);
    }

    public function test_can_update_story(): void
    {
        $story = Story::factory()->create();

        $response = $this->putJson("/api/stories/{$story->id}", [
            'title' => 'Updated Story',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Story']);
    }

    public function test_can_delete_story(): void
    {
        $story = Story::factory()->create();

        $response = $this->deleteJson("/api/stories/{$story->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('stories', ['id' => $story->id]);
    }

    public function test_story_belongs_to_epic_and_sprint(): void
    {
        $epic = Epic::factory()->create();
        $sprint = Sprint::factory()->create();
        $story = Story::factory()->create([
            'epic_id' => $epic->id,
            'sprint_id' => $sprint->id,
        ]);

        $response = $this->getJson("/api/stories/{$story->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'epic' => ['id', 'title'],
                'sprint' => ['id', 'title'],
            ]);
    }
}
