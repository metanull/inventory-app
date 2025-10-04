<?php

namespace Tests\Feature\Auth;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UserPermissionTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    public function test_user_can_be_assigned_role_with_permissions(): void
    {
        // Create a role with specific permissions
        $role = Role::create(['name' => 'Test Role']);
        $viewPermission = Permission::create(['name' => PermissionEnum::VIEW_DATA->value]);
        $createPermission = Permission::create(['name' => PermissionEnum::CREATE_DATA->value]);
        $role->givePermissionTo([$viewPermission, $createPermission]);

        $user = User::factory()->create();

        // Test: Assign role to user
        $user->assignRole($role);

        // Verify: User has the role and its permissions
        $this->assertTrue($user->hasRole('Test Role'));
        $this->assertTrue($user->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
        $this->assertTrue($user->hasPermissionTo(PermissionEnum::CREATE_DATA->value));

        // Verify: User doesn't have permissions not in the role
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::MANAGE_USERS->value));
    }

    public function test_user_can_be_given_direct_permissions(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => PermissionEnum::VIEW_DATA->value]);

        // Test: Give permission directly to user
        $user->givePermissionTo($permission);

        // Verify: User has the permission directly
        $this->assertTrue($user->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
        $this->assertTrue($user->hasDirectPermission(PermissionEnum::VIEW_DATA->value));
    }

    public function test_user_without_permissions_cannot_access_features(): void
    {
        $user = User::factory()->create();

        // Test: User with no permissions cannot access any features
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::VIEW_DATA->value));
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::CREATE_DATA->value));
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::MANAGE_USERS->value));
    }
}
