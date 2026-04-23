<?php

namespace Tests\Filament;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_can_access_the_filament_project_resource(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $response = $this->actingAs($user)->get('/admin/projects');

        $response
            ->assertOk()
            ->assertSee('Projects');
    }

    public function test_users_without_admin_panel_permission_receive_forbidden_on_the_filament_project_resource(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/admin/projects');

        $response->assertForbidden();
    }
}
