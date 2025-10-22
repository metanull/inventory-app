<?php

namespace Tests\Feature\Web;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Test that users cannot edit their own role assignments.
 */
class UserManagementRoleProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Administrator']);
        Role::create(['name' => 'Editor']);
        Role::create(['name' => 'Viewer']);
    }

    public function test_user_cannot_edit_own_roles(): void
    {
        $adminRole = Role::findByName('Administrator');
        $editorRole = Role::findByName('Editor');

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::MANAGE_USERS->value);
        $user->assignRole('Administrator');

        $this->actingAs($user);

        // Try to remove own Administrator role and add Editor role
        $response = $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$editorRole->id], // Try to change own role
        ]);

        $response->assertRedirect(route('admin.users.index'));

        // User should still have Administrator role (unchanged)
        $user->refresh();
        expect($user->hasRole('Administrator'))->toBeTrue();
        expect($user->hasRole('Editor'))->toBeFalse();
    }

    public function test_user_can_edit_other_users_roles(): void
    {
        $adminRole = Role::findByName('Administrator');
        $editorRole = Role::findByName('Editor');

        $adminUser = User::factory()->create();
        $adminUser->givePermissionTo(Permission::MANAGE_USERS->value);
        $adminUser->assignRole('Administrator');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('Viewer');

        $this->actingAs($adminUser);

        // Admin can change other user's roles
        $response = $this->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [$editorRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        // Target user should have Editor role now
        $targetUser->refresh();
        expect($targetUser->hasRole('Editor'))->toBeTrue();
        expect($targetUser->hasRole('Viewer'))->toBeFalse();
    }

    public function test_edit_page_shows_warning_when_editing_own_account(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::MANAGE_USERS->value);
        $user->assignRole('Administrator');

        $this->actingAs($user);

        $response = $this->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertSee('Cannot Edit Own Roles');
        $response->assertSee('You cannot modify your own role assignments for security reasons');
    }

    public function test_edit_page_shows_checkboxes_when_editing_other_user(): void
    {
        $adminUser = User::factory()->create();
        $adminUser->givePermissionTo(Permission::MANAGE_USERS->value);

        $targetUser = User::factory()->create();

        $this->actingAs($adminUser);

        $response = $this->get(route('admin.users.edit', $targetUser));

        $response->assertStatus(200);
        $response->assertDontSee('Cannot Edit Own Roles');
        $response->assertSee('name="roles[]"', false);
    }

    public function test_user_can_still_edit_own_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $user->givePermissionTo(Permission::MANAGE_USERS->value);
        $user->assignRole('Administrator');

        $this->actingAs($user);

        $response = $this->put(route('admin.users.update', $user), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user->refresh();
        expect($user->name)->toBe('New Name');
        expect($user->email)->toBe('new@example.com');
        expect($user->hasRole('Administrator'))->toBeTrue(); // Role unchanged
    }
}
