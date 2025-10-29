<?php

namespace Tests\Feature\Auth;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Web\Traits\CreatesTwoFactorUsers;
use Tests\Web\Traits\TestsFormValidation;

class RegistrationTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase, TestsFormValidation;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable self-registration for these tests
        \App\Models\Setting::set('self_registration_enabled', true, 'boolean');

        // Note: New users start with NO roles/permissions (permission-based system)
        // Admins must explicitly grant permissions for access

        // Mock email sending for all tests
        Mail::fake();
        Notification::fake();
    }

    public function test_user_can_register_with_valid_data(): void
    {
        Event::fake();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post(route('register.store'), $userData);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('secure-password123', $user->password));
        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_assigns_non_verified_users_role(): void
    {
        // Create permissions so we can check user doesn't have them
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => Permission::VIEW_DATA->value,
            'guard_name' => 'web',
        ]);
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => Permission::CREATE_DATA->value,
            'guard_name' => 'web',
        ]);
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => Permission::MANAGE_USERS->value,
            'guard_name' => 'web',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post(route('register.store'), $userData);

        $response->assertRedirect();

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);

        // Verify new user has NO roles or permissions
        // This is the permission-based system: users start with nothing
        $this->assertEquals(0, $user->roles()->count());
        $this->assertEquals(0, $user->getAllPermissions()->count());

        // Verify user does not have any specific permissions
        $this->assertFalse($user->hasPermissionTo(Permission::VIEW_DATA->value));
        $this->assertFalse($user->hasPermissionTo(Permission::CREATE_DATA->value));
        $this->assertFalse($user->hasPermissionTo(Permission::MANAGE_USERS->value));
    }

    public function test_registration_requires_name(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->assertFieldRequired(route('register.store'), 'name', $validData);
    }

    public function test_registration_requires_email(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->assertFieldRequired(route('register.store'), 'email', $validData);
    }

    public function test_registration_requires_valid_email(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->assertFieldValidEmail(route('register.store'), 'email', $validData);
    }

    public function test_registration_requires_unique_email(): void
    {
        $existingUser = $this->createUserWithoutTwoFactor([
            'email' => 'existing@example.com',
        ]);

        $validData = [
            'name' => 'John Doe',
            'email' => 'new@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->assertFieldUnique(route('register.store'), 'email', 'existing@example.com', $validData);
    }

    public function test_registration_requires_password(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->assertFieldRequired(route('register.store'), 'password', $validData);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post(route('register.store'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation_match(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->assertPasswordConfirmationMatch(route('register.store'), $validData);
    }

    public function test_registration_requires_minimum_password_length(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        // Laravel default is 8 characters minimum
        $this->assertFieldMinLength(route('register.store'), 'password', 8, $validData);
    }

    public function test_user_defaults_to_no_two_factor_after_registration(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->post(route('register.store'), $userData);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
    }

    public function test_registration_with_email_verification_required(): void
    {
        // Assuming email verification is enabled in your app
        config(['fortify.features' => ['email-verification']]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post(route('register.store'), $userData);

        $user = User::where('email', 'john@example.com')->first();

        // Should redirect to email verification page
        $response->assertRedirect(route('verification.notice'));

        // Email should not be verified yet
        $this->assertNull($user->email_verified_at);

        // Verification email should be sent
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_form_is_accessible(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertViewIs('auth.register');
    }

    public function test_authenticated_user_cannot_access_registration_form(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_registration_creates_user_with_proper_attributes(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->post(route('register.store'), $userData);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('secure-password123', $user->password));
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function test_registration_with_different_preferred_two_factor_method(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
            'preferred_2fa_method' => 'email',
        ];

        $this->post(route('register.store'), $userData);

        $user = User::where('email', 'john@example.com')->first();

        // 2FA is not enabled on registration
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
    }

    public function test_registration_is_blocked_when_self_registration_disabled(): void
    {
        // Disable self-registration
        \App\Models\Setting::set('self_registration_enabled', false, 'boolean');

        $userData = [
            'name' => 'John Doe',
            'email' => 'blocked@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        // Attempt to access registration page
        $getResponse = $this->get(route('register'));
        $getResponse->assertStatus(302);
        $getResponse->assertRedirect(route('login'));

        // Attempt to submit registration form
        // ValidationException redirects back to the form with errors
        $postResponse = $this->post(route('register.store'), $userData);
        $postResponse->assertStatus(302);
        $postResponse->assertRedirect(route('register'));
        $postResponse->assertSessionHasErrors(['registration']);

        // Verify user was NOT created
        $this->assertNull(User::where('email', 'blocked@example.com')->first());
    }
}
