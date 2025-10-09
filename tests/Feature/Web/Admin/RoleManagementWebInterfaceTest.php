<?php

namespace Tests\Feature\Web\Admin;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class RoleManagementWebInterfaceTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_user_with_manage_roles_permission_can_access_role_index(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertOk()
            ->assertSee('Role Management');
    }

    public function test_user_without_permission_cannot_access_role_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_manage_roles_permission_can_view_create_form(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $response = $this->actingAs($user)->get(route('admin.roles.create'));

        $response->assertOk()
            ->assertSee('Create New Role')
            ->assertSee('Role Name');
    }

    public function test_user_can_create_role_via_web_form(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $viewDataPermission = Permission::where('name', PermissionEnum::VIEW_DATA->value)->first();

        $response = $this->actingAs($user)->post(route('admin.roles.store'), [
            'name' => 'Test Web Role',
            'description' => 'Created via web form',
            'permissions' => [$viewDataPermission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'name' => 'Test Web Role',
            'description' => 'Created via web form',
        ]);

        $role = Role::where('name', 'Test Web Role')->first();
        $this->assertTrue($role->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
    }

    public function test_user_can_view_role_details(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Test Role', 'description' => 'Test description']);

        $response = $this->actingAs($user)->get(route('admin.roles.show', $role));

        $response->assertOk()
            ->assertSee('Test Role')
            ->assertSee('Test description');
    }

    public function test_user_can_edit_role_via_web_form(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Original Role', 'description' => 'Original description']);

        $response = $this->actingAs($user)->get(route('admin.roles.edit', $role));

        $response->assertOk()
            ->assertSee('Edit Role')
            ->assertSee('Original Role');
    }

    public function test_user_can_update_role_via_web_form(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Original Name']);
        $createDataPermission = Permission::where('name', PermissionEnum::CREATE_DATA->value)->first();

        $response = $this->actingAs($user)->put(route('admin.roles.update', $role), [
            'name' => 'Updated Name',
            'description' => 'Updated via form',
            'permissions' => [$createDataPermission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Updated Name',
            'description' => 'Updated via form',
        ]);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo(PermissionEnum::CREATE_DATA->value));
    }

    public function test_user_can_delete_role_via_web_form(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Role to Delete']);

        $response = $this->actingAs($user)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_cannot_delete_role_with_assigned_users(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Role with Users']);
        $targetUser = User::factory()->create();
        $targetUser->assignRole($role);

        $response = $this->actingAs($user)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_user_can_access_permissions_management_page(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Test Role']);

        $response = $this->actingAs($user)->get(route('admin.roles.permissions', $role));

        $response->assertOk()
            ->assertSee('Manage Permissions')
            ->assertSee('Test Role');
    }

    public function test_user_can_add_permission_to_role(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Test Role']);
        $permission = Permission::where('name', PermissionEnum::VIEW_DATA->value)->first();

        $response = $this->actingAs($user)->put(route('admin.roles.updatePermissions', $role), [
            'action' => 'add',
            'permission_id' => $permission->id,
        ]);

        $response->assertRedirect(route('admin.roles.permissions', $role))
            ->assertSessionHas('success');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
    }

    public function test_user_can_remove_permission_from_role(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Test Role']);
        $permission = Permission::where('name', PermissionEnum::VIEW_DATA->value)->first();
        $role->givePermissionTo($permission);

        $response = $this->actingAs($user)->put(route('admin.roles.updatePermissions', $role), [
            'action' => 'remove',
            'permission_id' => $permission->id,
        ]);

        $response->assertRedirect(route('admin.roles.permissions', $role))
            ->assertSessionHas('success');

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
    }

    public function test_user_can_sync_all_permissions_at_once(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        $role = Role::create(['name' => 'Test Role']);
        $viewPermission = Permission::where('name', PermissionEnum::VIEW_DATA->value)->first();
        $createPermission = Permission::where('name', PermissionEnum::CREATE_DATA->value)->first();

        // Initially give one permission
        $role->givePermissionTo($viewPermission);

        // Now sync to different set
        $response = $this->actingAs($user)->put(route('admin.roles.updatePermissions', $role), [
            'action' => 'sync',
            'permissions' => [$createPermission->id],
        ]);

        $response->assertRedirect(route('admin.roles.permissions', $role))
            ->assertSessionHas('success');

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
        $this->assertTrue($role->hasPermissionTo(PermissionEnum::CREATE_DATA->value));
    }

    public function test_role_search_works_correctly(): void
    {
        $user = $this->createUserWithPermissions([PermissionEnum::MANAGE_ROLES->value]);

        Role::create(['name' => 'Admin Role', 'description' => 'Administrator']);
        Role::create(['name' => 'User Role', 'description' => 'Regular user']);
        Role::create(['name' => 'Guest Role', 'description' => 'Guest access']);

        $response = $this->actingAs($user)->get(route('admin.roles.index', ['search' => 'Admin']));

        $response->assertOk()
            ->assertSee('Admin Role')
            ->assertDontSee('User Role')
            ->assertDontSee('Guest Role');
    }
}
