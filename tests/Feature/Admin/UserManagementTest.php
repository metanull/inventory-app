<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UserManagementTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    public function test_user_with_manage_users_permission_can_access_user_management_index(): void
    {
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_user_with_data_permissions_cannot_access_user_management(): void
    {
        // Create MANAGE_USERS permission (route checks for it even for denial)
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => Permission::MANAGE_USERS->value,
            'guard_name' => 'web',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create user with data permissions but no user management permissions
        $user = $this->createUserWithPermissions([Permission::VIEW_DATA->value]);

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_user_without_permissions_cannot_access_user_management(): void
    {
        // Create the MANAGE_USERS permission (route checks for it)
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => Permission::MANAGE_USERS->value,
            'guard_name' => 'web',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = $this->createUnprivilegedUser();

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_user_manager_can_view_user_details(): void
    {
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);
        $targetUser = $this->createUserWithPermissions([Permission::VIEW_DATA->value]);
        $targetUser->update(['name' => 'John Doe']);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.show', $targetUser));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee(Permission::VIEW_DATA->value);
    }

    public function test_manager_can_create_new_user(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        // Create a role to assign to the new user
        $regularRole = \Spatie\Permission\Models\Role::create([
            'name' => 'Regular User',
            'guard_name' => 'web',
        ]);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$regularRole->id],
        ];

        $response = $this->actingAs($manager)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole('Regular User'));
    }

    public function test_manager_can_update_user(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $targetUser = User::factory()->create(['name' => 'Old Name']);

        // Create roles for assignment
        $managerRole = \Spatie\Permission\Models\Role::create(['name' => 'Manager Role', 'guard_name' => 'web']);

        $updateData = [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'roles' => [$managerRole->id],
        ];

        $response = $this->actingAs($manager)
            ->put(route('admin.users.update', $targetUser), $updateData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertEquals('Updated Name', $targetUser->name);
        $this->assertTrue($targetUser->hasRole('Manager Role'));
    }

    public function test_manager_can_delete_other_users(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $targetUser = User::factory()->create();
        $targetUserId = $targetUser->id;

        $response = $this->actingAs($manager)
            ->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $targetUserId]);
    }

    public function test_manager_cannot_delete_themselves(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $response = $this->actingAs($manager)
            ->delete(route('admin.users.destroy', $manager));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $manager->id]);
    }

    public function test_search_functionality_works(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        // Create test users with unique, easily identifiable names
        $aliceUser = User::factory()->create([
            'name' => 'Alice SearchTest Smith',
            'email' => 'alice.searchtest@example.com',
        ]);
        
        $bobUser = User::factory()->create([
            'name' => 'Bob SearchTest Jones',
            'email' => 'bob.searchtest@example.com',
        ]);

        // Search for Alice - should only return Alice
        $response = $this->actingAs($manager)
            ->get(route('admin.users.index', ['search' => 'Alice SearchTest']));

        $response->assertStatus(200);
        
        // Assert Alice is in the results
        $response->assertSee('Alice SearchTest Smith');
        $response->assertSee('alice.searchtest@example.com');
        
        // Assert Bob is NOT in the results by checking both name and email
        $response->assertDontSee('Bob SearchTest Jones');
        $response->assertDontSee('bob.searchtest@example.com');
        
        // Verify the search also works by email
        $response = $this->actingAs($manager)
            ->get(route('admin.users.index', ['search' => 'bob.searchtest']));
            
        $response->assertStatus(200);
        $response->assertSee('Bob SearchTest Jones');
        $response->assertDontSee('Alice SearchTest Smith');
    }

    public function test_role_filter_works(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        // Create a test role
        $regularRole = \Spatie\Permission\Models\Role::create(['name' => 'Regular User', 'guard_name' => 'web']);

        $regularUser = User::factory()->create();
        $regularUser->assignRole($regularRole);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.index', ['role' => 'Regular User']));

        $response->assertStatus(200);
        $response->assertSee($regularUser->name);
        $response->assertDontSee($manager->name);
    }

    public function test_admin_can_verify_user_email_through_edit_form(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $targetUser = User::factory()->create(['email_verified_at' => null]);
        $this->assertFalse($targetUser->hasVerifiedEmail());

        $response = $this->actingAs($manager)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'verify_email' => '1',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue($targetUser->fresh()->hasVerifiedEmail());
    }

    public function test_admin_can_unverify_user_email_through_edit_form(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $this->assertTrue($targetUser->hasVerifiedEmail());

        $response = $this->actingAs($manager)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'unverify_email' => '1',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($targetUser->fresh()->hasVerifiedEmail());
    }

    public function test_admin_user_edit_form_shows_email_verification_status(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        // Test with verified user
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $response = $this->actingAs($manager)->get(route('admin.users.edit', $verifiedUser));
        $response->assertStatus(200);
        $response->assertSee('✅ Verified');
        $response->assertSee('Remove verification');

        // Test with unverified user
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);
        $response = $this->actingAs($manager)->get(route('admin.users.edit', $unverifiedUser));
        $response->assertStatus(200);
        $response->assertSee('❌ Not Verified');
        $response->assertSee('Mark as verified');
    }

    public function test_user_show_page_displays_edit_link_instead_of_manage_roles(): void
    {
        // Create manager with MANAGE_USERS permission
        $manager = $this->createUserWithPermissions([Permission::MANAGE_USERS->value]);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($manager)->get(route('admin.users.show', $targetUser));

        $response->assertStatus(200);
        $response->assertSee('Edit User'); // Button text in the view
        $response->assertDontSee('Manage Roles');
    }
}
