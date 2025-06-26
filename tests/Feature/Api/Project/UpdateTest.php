<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_allows_authenticated_users(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make()->except(['id']);
        $response = $this->putJson(route('project.update', $project), $data);
        $response->assertOk();
    }

    public function test_update_updates_a_row(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make()->except(['id']);
        $response = $this->putJson(route('project.update', $project), $data);
        $response->assertOk();
        $this->assertDatabaseHas('projects', array_merge(['id' => $project->id], $data));
    }

    public function test_update_returns_ok_on_success(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make()->except(['id']);
        $response = $this->putJson(route('project.update', $project), $data);
        $response->assertOk();
    }

    public function test_update_returns_not_found_when_record_does_not_exist(): void
    {
        $data = Project::factory()->make()->except(['id']);
        $response = $this->putJson(route('project.update', 'non-existent-id'), $data);
        $response->assertNotFound();
    }

    public function test_update_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $project = Project::factory()->create();
        $invalidData = Project::factory()->make()->except(['internal_name']);
        $response = $this->putJson(route('project.update', $project), $invalidData);
        $response->assertUnprocessable();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make()->except(['id']);
        $response = $this->putJson(route('project.update', $project), $data);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'launch_date',
                'is_launched',
                'is_enabled',
                'context',
                'language',
                'created_at',
                'updated_at',
            ]
        ]);
    }

    public function test_update_returns_the_expected_data(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make()->except(['id']);
        $response = $this->putJson(route('project.update', $project), $data);
        $response->assertOk();
        $response->assertJsonPath('data.id', $project->id);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $this->assertDatabaseHas('projects', array_merge(['id' => $project->id], $data));
    }

    public function test_update_validates_its_input(): void
    {
        $project = Project::factory()->create();
        $invalidData = Project::factory()->make()->except(['internal_name']); // Missing required field
        $response = $this->putJson(route('project.update', $project), $invalidData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}
