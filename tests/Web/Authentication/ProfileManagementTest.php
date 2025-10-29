<?php

namespace Tests\Feature\Auth;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Test profile management features related to permissions.
 * These tests verify the FEATURE of displaying permissions to users.
 */
class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_displays_user_permissions(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $viewPermission = PermissionModel::findByName(Permission::VIEW_DATA->value);
        $createPermission = PermissionModel::findByName(Permission::CREATE_DATA->value);

        // Create role with permissions
        $role = Role::create(['name' => 'Test Role', 'guard_name' => 'web']);
        $role->givePermissionTo([$viewPermission, $createPermission]);

        $user = User::factory()->create();
        $user->assignRole($role);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)
            ->get(route('web.profile.show'));

        $response->assertStatus(200);
        $response->assertSee('User Roles & Permissions');
        $response->assertSee('Test Role');
        $response->assertSee(Permission::VIEW_DATA->value);
        $response->assertSee(Permission::CREATE_DATA->value);
    }

    public function test_profile_shows_warning_for_users_without_permissions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('web.profile.show'));

        $response->assertStatus(200);
        $response->assertSee('No Roles Assigned');
        $response->assertSee('Please contact an administrator');
    }

    public function test_user_role_information_livewire_component_displays_permissions(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $managePermission = PermissionModel::findByName(Permission::MANAGE_USERS->value);
        $assignPermission = PermissionModel::findByName(Permission::ASSIGN_ROLES->value);

        // Create role with permissions
        $role = Role::create(['name' => 'Test Manager Role', 'guard_name' => 'web']);
        $role->givePermissionTo([$managePermission, $assignPermission]);

        $user = User::factory()->create();
        $user->assignRole($role);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Profile\UserRoleInformation::class)
            ->assertSee('Test Manager Role')
            ->assertSee(Permission::MANAGE_USERS->value)
            ->assertSee(Permission::ASSIGN_ROLES->value)
            ->assertDontSee(Permission::VIEW_DATA->value); // This role doesn't have data permissions
    }

    public function test_user_role_information_shows_no_roles_message(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Profile\UserRoleInformation::class)
            ->assertSee('No Roles Assigned')
            ->assertSee('Please contact an administrator');
    }

    public function test_email_verification_is_enabled(): void
    {
        $this->assertTrue(
            in_array('email-verification', config('fortify.features'))
        );
    }

    public function test_profile_update_features_are_enabled(): void
    {
        $this->assertTrue(
            in_array('update-profile-information', config('fortify.features'))
        );

        $this->assertTrue(
            in_array('update-passwords', config('fortify.features'))
        );
    }

    public function test_two_factor_authentication_is_enabled(): void
    {
        $this->assertTrue(
            in_array('two-factor-authentication', config('fortify.features'))
        );
    }
}
