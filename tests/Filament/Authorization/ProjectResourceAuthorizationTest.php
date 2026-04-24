<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_project_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $dashboard = $this->actingAs($user)->get('/admin');

        $dashboard
            ->assertOk()
            ->assertDontSee('Projects');

        $this->actingAs($user)->get('/admin/projects')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_project_index_and_view_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $project = Project::factory()->create(['internal_name' => 'Temple catalogue']);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Projects');

        $this->actingAs($user)->get('/admin/projects')
            ->assertOk()
            ->assertSee('Temple catalogue');

        $this->actingAs($user)->get("/admin/projects/{$project->getKey()}")
            ->assertOk()
            ->assertSee('Temple catalogue');

        $this->actingAs($user)->get('/admin/projects/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/projects/{$project->getKey()}/edit")
            ->assertForbidden();
    }
}
