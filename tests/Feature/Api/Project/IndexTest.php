<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_allows_authenticated_users(): void
    {
        $response = $this->getJson(route('project.index'));
        $response->assertOk();
    }

    public function test_index_returns_ok_on_success(): void
    {
        $response = $this->getJson(route('project.index'));
        $response->assertOk();
    }

    public function test_index_returns_the_expected_structure(): void
    {
        Project::factory()->create();
        $response = $this->getJson(route('project.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ]
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.index'));
        $response->assertOk();
        $response->assertJsonPath('data.0.id', $project->id);
        $response->assertJsonPath('data.0.internal_name', $project->internal_name);
    }
}
