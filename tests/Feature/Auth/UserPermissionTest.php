<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_can_be_assigned_role_and_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::findByName('Regular User');

        // Assign role to user
        $user->assignRole($role);

        // Check user has role
        $this->assertTrue($user->hasRole('Regular User'));

        // Check user has permissions from role
        $this->assertTrue($user->hasPermissionTo('view data'));
        $this->assertTrue($user->hasPermissionTo('create data'));
        $this->assertTrue($user->hasPermissionTo('update data'));
        $this->assertTrue($user->hasPermissionTo('delete data'));

        // Check user doesn't have permissions they shouldn't have
        $this->assertFalse($user->hasPermissionTo('manage users'));
    }

    public function test_user_can_be_given_direct_permissions(): void
    {
        $user = User::factory()->create();
        $permission = Permission::findByName('view data');

        // Give permission directly to user
        $user->givePermissionTo($permission);

        // Check user has permission
        $this->assertTrue($user->hasPermissionTo('view data'));
        $this->assertTrue($user->hasDirectPermission('view data'));
    }

    public function test_user_with_manager_role_has_user_management_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::findByName('Manager of Users');

        $user->assignRole($role);

        // Check user management permissions
        $this->assertTrue($user->hasPermissionTo('manage users'));
        $this->assertTrue($user->hasPermissionTo('assign roles'));
        $this->assertTrue($user->hasPermissionTo('view user management'));
        $this->assertTrue($user->hasPermissionTo('manage roles'));
        $this->assertTrue($user->hasPermissionTo('view role management'));

        // Should NOT have data permissions
        $this->assertFalse($user->hasPermissionTo('view data'));
        $this->assertFalse($user->hasPermissionTo('create data'));
        $this->assertFalse($user->hasPermissionTo('update data'));
        $this->assertFalse($user->hasPermissionTo('delete data'));
    }

    public function test_user_without_roles_has_no_permissions(): void
    {
        $user = User::factory()->create();

        // User has no roles
        $this->assertEquals(0, $user->roles()->count());

        // User has no permissions
        $this->assertFalse($user->hasPermissionTo('view data'));
        $this->assertFalse($user->hasPermissionTo('create data'));
        $this->assertFalse($user->hasPermissionTo('manage users'));
    }
}
