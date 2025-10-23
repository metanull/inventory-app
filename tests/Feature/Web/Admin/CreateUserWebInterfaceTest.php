<?php

namespace Tests\Feature\Web\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateUserWebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_admin_can_create_user_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Test User',
            'email' => 'newuser@example.com',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');
        $response->assertSessionHas('user_name', 'New Test User');
        $response->assertSessionHas('user_email', 'newuser@example.com');

        // Verify user was created
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('New Test User', $user->name);
    }

    public function test_admin_can_create_user_with_roles_via_web_form(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $regularUserRole = Role::where('name', 'Regular User')->first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User With Role',
            'email' => 'userrole@example.com',
            'roles' => [$regularUserRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify user was created with role
        $user = User::where('email', 'userrole@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Regular User'));
    }

    public function test_admin_can_create_user_with_multiple_roles(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $regularUserRole = Role::where('name', 'Regular User')->first();
        $visitorRole = Role::where('name', 'Visitor')->first(); // Use non-sensitive role

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Multi Role User',
            'email' => 'multirole@example.com',
            'roles' => [$regularUserRole->id, $visitorRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'multirole@example.com')->first();
        $this->assertTrue($user->hasRole('Regular User'));
        $this->assertTrue($user->hasRole('Visitor'));
    }

    public function test_create_user_form_validation_works(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        // Test missing required fields
        $response = $this->actingAs($admin)->post(route('admin.users.store'), []);
        $response->assertSessionHasErrors(['name', 'email']);

        // Test invalid email
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test duplicate email
        $existingUser = User::factory()->create();
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test User',
            'email' => $existingUser->email,
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_create_user_with_invalid_role_ids_fails(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [99999], // Non-existent role ID
        ]);

        $response->assertSessionHasErrors('roles.0');
    }

    public function test_generated_password_is_secure(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Password Test User',
            'email' => 'passwordtest@example.com',
            'roles' => [],
        ]);

        $generatedPassword = session('generated_password');

        // Verify password meets security requirements
        $this->assertNotNull($generatedPassword);
        $this->assertGreaterThanOrEqual(16, strlen($generatedPassword));

        // Verify user can authenticate with generated password
        $user = User::where('email', 'passwordtest@example.com')->first();
        $this->assertTrue(Hash::check($generatedPassword, $user->password));
    }

    public function test_regular_user_cannot_create_users(): void
    {
        $regularUser = User::factory()->create(['email_verified_at' => now()]);
        $regularUser->assignRole('Regular User');

        $response = $this->actingAs($regularUser)->post(route('admin.users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@example.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_users(): void
    {
        $response = $this->post(route('admin.users.store'), [
            'name' => 'Unauthenticated User',
            'email' => 'unauth@example.com',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_create_user_form_displays_correctly(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Create User');
        $response->assertSee('Password Generation');
        $response->assertSee('A secure password will be automatically generated');
        $response->assertDontSee('type="password"'); // Should not have password input fields
    }

    public function test_password_generation_notification_displays_correctly(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        // Create user to trigger password notification
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Notification Test User',
            'email' => 'notification@example.com',
            'roles' => [],
        ]);

        // Check that the index page shows the notification
        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Password Generated Successfully');
        $response->assertSee('Notification Test User');
        $response->assertSee('notification@example.com');
        $response->assertSee('Generated Password:');
    }

    public function test_cannot_create_user_with_sensitive_permissions_without_mfa(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $managerRole = Role::where('name', 'Manager of Users')->first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test Manager',
            'email' => 'testmanager@example.com',
            'roles' => [$managerRole->id],
        ]);

        $response->assertSessionHasErrors(['roles']);
        // Verify the error message contains the key phrase
        $errors = session('errors');
        $this->assertNotNull($errors);
        $roleErrors = $errors->get('roles');
        $this->assertNotEmpty($roleErrors);
        $this->assertStringContainsString('Cannot assign roles with sensitive permissions', $roleErrors[0]);

        // Verify user was not created
        $user = User::where('email', 'testmanager@example.com')->first();
        $this->assertNull($user);
    }
}
