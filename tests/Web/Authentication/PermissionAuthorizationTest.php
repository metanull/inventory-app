<?php

namespace Tests\Web\Authentication;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as PermissionModel;
use Tests\TestCase;

/**
 * Test that the permission-based authorization system works correctly.
 * These tests verify FEATURES, not data structure.
 */
class PermissionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users without VIEW_DATA permission cannot access data routes
     */
    public function test_user_without_view_permission_cannot_access_data_routes(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/web/items');

        $response->assertStatus(403);
    }

    /**
     * Test that users with VIEW_DATA permission can access data routes
     */
    public function test_user_with_view_permission_can_access_data_routes(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::VIEW_DATA->value);

        // Create user and assign permission directly
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get('/web/items');

        $response->assertOk();
    }

    /**
     * Test that MANAGE_USERS permission allows user management access
     */
    public function test_user_with_manage_users_permission_can_access_admin(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::MANAGE_USERS->value);

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertOk();
    }

    /**
     * Test that users without MANAGE_USERS permission cannot access admin
     */
    public function test_user_without_manage_users_permission_cannot_access_admin(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that MANAGE_SETTINGS permission allows settings access
     */
    public function test_user_with_manage_settings_permission_can_access_settings(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::MANAGE_SETTINGS->value);

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertOk();
    }

    /**
     * Test that users without MANAGE_SETTINGS permission cannot access settings
     */
    public function test_user_without_manage_settings_permission_cannot_access_settings(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that permission middleware correctly isolates different permissions
     */
    public function test_permission_middleware_correctly_isolates_permissions(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $viewPermission = PermissionModel::findByName(Permission::VIEW_DATA->value);
        $managePermission = PermissionModel::findByName(Permission::MANAGE_USERS->value);

        // User with only view permission
        $viewUser = User::factory()->create();
        $viewUser->givePermissionTo($viewPermission);

        // User with only manage permission
        $manageUser = User::factory()->create();
        $manageUser->givePermissionTo($managePermission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // View user can access data routes
        $response = $this->actingAs($viewUser)->get('/web/items');
        $response->assertOk();

        // View user cannot access admin routes
        $response = $this->actingAs($viewUser)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Manage user can access admin routes
        $response = $this->actingAs($manageUser)->get(route('admin.users.index'));
        $response->assertOk();

        // Manage user cannot access data routes (no view data permission)
        $response = $this->actingAs($manageUser)->get('/web/items');
        $response->assertStatus(403);
    }

    /**
     * Test that users can have multiple permissions
     */
    public function test_user_with_multiple_permissions_can_access_multiple_routes(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $viewPermission = PermissionModel::findByName(Permission::VIEW_DATA->value);
        $managePermission = PermissionModel::findByName(Permission::MANAGE_USERS->value);

        $user = User::factory()->create();
        $user->givePermissionTo([$viewPermission, $managePermission]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Can access both data and admin routes
        $response = $this->actingAs($user)->get('/web/items');
        $response->assertOk();

        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertOk();
    }
}
