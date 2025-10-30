<?php

namespace Tests\Web\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementWebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_admin_can_assign_roles_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();
        $regularUserRole = Role::where('name', 'Regular User')->first();

        // Submit the form with role IDs (as the web form does)
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [$regularUserRole->id], // This should work with role IDs
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify the role was actually assigned
        $this->assertTrue($targetUser->fresh()->hasRole('Regular User'));
    }

    public function test_admin_can_assign_multiple_roles_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();
        // Enable TOTP for the target user so they can receive sensitive roles
        $targetUser->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $regularUserRole = Role::where('name', 'Regular User')->first();
        $managerRole = Role::where('name', 'Manager of Users')->first();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [$regularUserRole->id, $managerRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $freshUser = $targetUser->fresh();
        $this->assertTrue($freshUser->hasRole('Regular User'));
        $this->assertTrue($freshUser->hasRole('Manager of Users'));
    }

    public function test_admin_can_remove_all_roles_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('Regular User');
        $this->assertTrue($targetUser->hasRole('Regular User'));

        // Submit form with no roles selected
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            // No 'roles' key means no roles selected
        ]);

        $response->assertRedirect(route('admin.users.index'));

        // Verify all roles were removed
        $this->assertFalse($targetUser->fresh()->hasRole('Regular User'));
        $this->assertCount(0, $targetUser->fresh()->roles);
    }

    public function test_admin_can_update_user_info_and_roles_simultaneously(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $regularUserRole = Role::where('name', 'Regular User')->first();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'roles' => [$regularUserRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $freshUser = $targetUser->fresh();
        $this->assertEquals('New Name', $freshUser->name);
        $this->assertEquals('new@example.com', $freshUser->email);
        $this->assertTrue($freshUser->hasRole('Regular User'));
    }

    public function test_admin_can_generate_new_user_password_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();
        $originalPasswordHash = $targetUser->password;

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('generated_password');

        // Verify password was changed
        $this->assertNotEquals($originalPasswordHash, $targetUser->fresh()->password);
    }

    public function test_admin_can_verify_email_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => null]);
        $this->assertFalse($targetUser->hasVerifiedEmail());

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'verify_email' => '1',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue($targetUser->fresh()->hasVerifiedEmail());
    }

    public function test_admin_can_unverify_email_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $this->assertTrue($targetUser->hasVerifiedEmail());

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'unverify_email' => '1',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($targetUser->fresh()->hasVerifiedEmail());
    }

    public function test_form_validation_prevents_invalid_role_ids(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [99999], // Non-existent role ID
        ]);

        $response->assertSessionHasErrors('roles.0');
    }

    public function test_form_validation_prevents_duplicate_email(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => 'existing@example.com', // This email already exists
            'roles' => [],
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_is_not_changed_when_generation_checkbox_not_checked(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();
        $originalPasswordHash = $targetUser->password;

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'roles' => [],
            // No generate_new_password checkbox
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionMissing('generated_password');

        // Verify password was NOT changed
        $this->assertEquals($originalPasswordHash, $targetUser->fresh()->password);
        $this->assertEquals('Updated Name', $targetUser->fresh()->name);
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $regularUser = User::factory()->create(['email_verified_at' => now()]);
        $regularUser->assignRole('Regular User');

        $targetUser = User::factory()->create();

        $response = $this->actingAs($regularUser)->put(route('admin.users.update', $targetUser), [
            'name' => 'Hacked Name',
            'email' => $targetUser->email,
            'roles' => [],
        ]);

        $response->assertStatus(403);
    }
}
