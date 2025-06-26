<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_allows_authenticated_users(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.show', $project));
        $response->assertOk();
    }

    public function test_show_returns_ok_on_success(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.show', $project));
        $response->assertOk();
    }

    public function test_show_returns_not_found_when_record_does_not_exist(): void
    {
        $response = $this->getJson(route('project.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_show_returns_the_expected_structure(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.show', $project));
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

    public function test_show_returns_the_expected_data(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.show', $project));
        $response->assertOk();
        $response->assertJsonPath('data.id', $project->id);
        $response->assertJsonPath('data.internal_name', $project->internal_name);
        $response->assertJsonPath('data.backward_compatibility', $project->backward_compatibility);
    }
}
