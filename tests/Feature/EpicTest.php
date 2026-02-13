<?php

namespace Tests\Feature;

use App\Models\Epic;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpicTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_epic(): void
    {
        $response = $this->postJson('/api/epics', [
            'title' => 'User Authentication Epic',
            'description' => 'Implement user authentication',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'User Authentication Epic',
                'description' => 'Implement user authentication',
            ]);
    }

    public function test_can_list_epics(): void
    {
        Epic::factory()->count(3)->create();

        $response = $this->getJson('/api/epics');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_show_epic(): void
    {
        $epic = Epic::factory()->create([
            'title' => 'Test Epic',
        ]);

        $response = $this->getJson("/api/epics/{$epic->id}");

        $response->assertStatus(200)
            ->assertJson(['title' => 'Test Epic']);
    }

    public function test_can_update_epic(): void
    {
        $epic = Epic::factory()->create();

        $response = $this->putJson("/api/epics/{$epic->id}", [
            'title' => 'Updated Epic',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Epic']);
    }

    public function test_can_delete_epic(): void
    {
        $epic = Epic::factory()->create();

        $response = $this->deleteJson("/api/epics/{$epic->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('epics', ['id' => $epic->id]);
    }

    public function test_epic_has_many_stories(): void
    {
        $epic = Epic::factory()->create();
        Story::factory()->count(2)->create(['epic_id' => $epic->id]);

        $response = $this->getJson("/api/epics/{$epic->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'stories');
    }
}
