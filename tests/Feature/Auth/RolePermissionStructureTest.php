<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_it_creates_all_required_permissions(): void
    {
        $expectedPermissions = [
            'view data',
            'create data',
            'update data',
            'delete data',
            'manage users',
            'assign roles',
            'view user management',
            'manage roles',
            'view role management',
        ];

        foreach ($expectedPermissions as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
            ]);
        }

        $this->assertEquals(count($expectedPermissions), Permission::count());
    }

    public function test_it_creates_required_roles(): void
    {
        $this->assertDatabaseHas('roles', [
            'name' => 'Non-verified users',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Regular User',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager of Users',
        ]);

        $this->assertEquals(3, Role::count());
    }

    public function test_regular_user_role_has_correct_permissions(): void
    {
        $regularUser = Role::findByName('Regular User');

        $expectedPermissions = [
            'view data',
            'create data',
            'update data',
            'delete data',
        ];

        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($regularUser->hasPermissionTo($permission));
        }

        // Should not have user management permissions
        $this->assertFalse($regularUser->hasPermissionTo('manage users'));
        $this->assertFalse($regularUser->hasPermissionTo('assign roles'));
        $this->assertFalse($regularUser->hasPermissionTo('view user management'));
    }

    public function test_non_verified_users_role_has_no_permissions(): void
    {
        $nonVerifiedRole = Role::findByName('Non-verified users');

        // Should have no permissions at all
        $this->assertEquals(0, $nonVerifiedRole->permissions()->count());

        // Explicitly check that it doesn't have any of the main permissions
        $this->assertFalse($nonVerifiedRole->hasPermissionTo('view data'));
        $this->assertFalse($nonVerifiedRole->hasPermissionTo('create data'));
        $this->assertFalse($nonVerifiedRole->hasPermissionTo('manage users'));
    }

    public function test_manager_role_has_only_user_management_permissions(): void
    {
        $manager = Role::findByName('Manager of Users');

        // Should have user/role management permissions
        $managementPermissions = [
            'manage users',
            'assign roles',
            'view user management',
            'manage roles',
            'view role management',
        ];

        foreach ($managementPermissions as $permission) {
            $this->assertTrue($manager->hasPermissionTo($permission));
        }

        // Should NOT have data operation permissions
        $dataPermissions = [
            'view data',
            'create data',
            'update data',
            'delete data',
        ];

        foreach ($dataPermissions as $permission) {
            $this->assertFalse($manager->hasPermissionTo($permission));
        }
    }

    public function test_permissions_have_descriptions(): void
    {
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $this->assertNotNull($permission->description);
            $this->assertNotEmpty($permission->description);
        }
    }

    public function test_roles_have_descriptions(): void
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            $this->assertNotNull($role->description);
            $this->assertNotEmpty($role->description);
        }
    }
}
