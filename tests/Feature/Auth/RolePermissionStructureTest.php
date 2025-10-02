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
            'name' => 'Regular User',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager of Users',
        ]);

        $this->assertEquals(2, Role::count());
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

    public function test_manager_role_has_all_permissions(): void
    {
        $manager = Role::findByName('Manager of Users');

        $allPermissions = [
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

        foreach ($allPermissions as $permission) {
            $this->assertTrue($manager->hasPermissionTo($permission));
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
