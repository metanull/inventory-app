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

    /** @test */
    public function it_creates_all_required_permissions()
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

    /** @test */
    public function it_creates_required_roles()
    {
        $this->assertDatabaseHas('roles', [
            'name' => 'Regular User',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager of Users',
        ]);

        $this->assertEquals(2, Role::count());
    }

    /** @test */
    public function regular_user_role_has_correct_permissions()
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

    /** @test */
    public function manager_role_has_all_permissions()
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

    /** @test */
    public function permissions_have_descriptions()
    {
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $this->assertNotNull($permission->description);
            $this->assertNotEmpty($permission->description);
        }
    }

    /** @test */
    public function roles_have_descriptions()
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            $this->assertNotNull($role->description);
            $this->assertNotEmpty($role->description);
        }
    }
}
