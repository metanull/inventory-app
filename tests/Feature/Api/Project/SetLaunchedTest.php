<?php

namespace Tests\Feature\Api\Project;

use App\Enums\Permission;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class SetLaunchedTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_set_enabled_allows_authenticated_user(): void
    {
        $project = Project::factory()->create();
        $response = $this->patchJson(route('project.setLaunched', $project), [
            'is_launched' => true,
        ]);
        $response->assertOk();
    }

    public function test_set_enabled_updates_a_row_toggle_on(): void
    {
        $project = Project::factory()->create();
        $response = $this->patchJson(route('project.setLaunched', $project), [
            'is_launched' => true,
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'is_launched' => 1,
        ]);
    }

    public function test_set_enabled_updates_a_row_toggle_off(): void
    {
        $project = Project::factory()->withLaunched()->create();
        $response = $this->patchJson(route('project.setLaunched', $project), [
            'is_launched' => false,
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'is_launched' => 0,
        ]);
    }
}
