<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_manage_roles_permission_cannot_see_roles_resource(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Roles');

        $this->actingAs($user)->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_users_with_manage_roles_permission_can_access_roles_index(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_ROLES->value,
        ]);

        $this->actingAs($user)->get('/admin/roles')
            ->assertOk();
    }

    public function test_users_with_manage_roles_permission_can_create_roles(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_ROLES->value,
        ]);

        $this->actingAs($user)->get('/admin/roles/create')
            ->assertOk();
    }

    public function test_users_with_manage_roles_permission_can_edit_roles(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_ROLES->value,
        ]);

        $role = Role::firstOrCreate(['name' => 'Test Role', 'guard_name' => 'web']);

        $this->actingAs($user)->get("/admin/roles/{$role->getKey()}/edit")
            ->assertOk();
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get('/admin/roles')
            ->assertRedirect('/admin/login');
    }
}
