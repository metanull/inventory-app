<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
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
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.index'));
        $response->assertOk();
        $response->assertJsonPath('data.0.id', $project->id);
        $response->assertJsonPath('data.0.internal_name', $project->internal_name);
        $response->assertJsonPath('meta.total', 1);
    }
}
