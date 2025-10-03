<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAssignmentBugFixVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_create_user_with_roles_does_not_throw_role_does_not_exist_error(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $regularUserRole = Role::where('name', 'Regular User')->first();
        $managerRole = Role::where('name', 'Manager of Users')->first();

        // This should NOT throw "There is no role named `X` for guard `web`" error
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Bug Fix Test User',
            'email' => 'bugfix@example.com',
            'roles' => [$regularUserRole->id, $managerRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');

        // Verify user was created with correct roles
        $user = User::where('email', 'bugfix@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Regular User'));
        $this->assertTrue($user->hasRole('Manager of Users'));
    }

    public function test_edit_user_with_roles_does_not_throw_role_does_not_exist_error(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        $managerRole = Role::where('name', 'Manager of Users')->first();

        // This should NOT throw "There is no role named `X` for guard `web`" error
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [$managerRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify roles were updated correctly
        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('Manager of Users'));
        $this->assertFalse($targetUser->hasRole('Regular User'));
    }

    public function test_create_user_with_empty_roles_works(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        // This should work without errors
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'No Roles User',
            'email' => 'noroles@example.com',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify user was created with no roles
        $user = User::where('email', 'noroles@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(0, $user->roles->count());
    }

    public function test_edit_user_removing_all_roles_works(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        // This should work without errors
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify all roles were removed
        $targetUser->refresh();
        $this->assertEquals(0, $targetUser->roles->count());
    }

    public function test_password_generation_works_in_both_create_and_edit(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        // Test create user password generation
        $createResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Password Gen Test',
            'email' => 'passgen@example.com',
            'roles' => [],
        ]);

        $createResponse->assertRedirect(route('admin.users.index'));
        $createResponse->assertSessionHas('generated_password');
        $createPassword = session('generated_password');

        $user = User::where('email', 'passgen@example.com')->first();
        $this->assertNotNull($user);
        $this->assertGreaterThanOrEqual(16, strlen($createPassword));

        // Test edit user password generation
        $editResponse = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'generate_new_password' => '1',
            'roles' => [],
        ]);

        $editResponse->assertRedirect(route('admin.users.index'));
        $editResponse->assertSessionHas('generated_password');
        $editPassword = session('generated_password');

        // Verify passwords are different and both are secure
        $this->assertNotEquals($createPassword, $editPassword);
        $this->assertGreaterThanOrEqual(16, strlen($editPassword));
    }

    public function test_role_assignment_with_password_generation_works_together(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $regularUserRole = Role::where('name', 'Regular User')->first();
        $managerRole = Role::where('name', 'Manager of Users')->first();

        // Create user with roles and password generation
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Combined Test User',
            'email' => 'combined@example.com',
            'roles' => [$regularUserRole->id, $managerRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');

        // Verify both features work
        $user = User::where('email', 'combined@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Regular User'));
        $this->assertTrue($user->hasRole('Manager of Users'));

        $generatedPassword = session('generated_password');
        $this->assertGreaterThanOrEqual(16, strlen($generatedPassword));
    }
}
