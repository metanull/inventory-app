<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Projects;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_update_persists_changes_and_redirects(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Old Name',
            'is_enabled' => false,
        ]);

        $response = $this->put(route('projects.update', $project), [
            'internal_name' => 'New Name',
            'is_enabled' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'internal_name' => 'New Name',
            'is_enabled' => true,
        ]);
    }
}
