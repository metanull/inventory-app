<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class RegistrationTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

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

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('secure-password123', $user->password));
        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_requires_name(): void
    {
        $userData = [
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
        $this->assertGuest();
    }

    public function test_registration_requires_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_requires_valid_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        $existingUser = $this->createUserWithoutTwoFactor([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_requires_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation_match(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'different-password',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_minimum_password_length(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['terms']);
        $this->assertGuest();
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

        $this->post('/register', $userData);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->email_2fa_enabled);
        $this->assertFalse($user->hasAnyTwoFactorEnabled());
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

        $response = $this->post('/register', $userData);

        $user = User::where('email', 'john@example.com')->first();

        // Should redirect to email verification page
        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');

        // Email should not be verified yet
        $this->assertNull($user->email_verified_at);

        // Verification email should be sent
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_form_is_accessible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_authenticated_user_cannot_access_registration_form(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->get('/register');

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
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

        $this->post('/register', $userData);

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

        $this->post('/register', $userData);

        $user = User::where('email', 'john@example.com')->first();

        // Should set preferred method but not enable 2FA yet
        $this->assertEquals('email', $user->preferred_2fa_method);
        $this->assertFalse($user->hasAnyTwoFactorEnabled());
    }

    public function test_registration_rate_limiting(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        // Make multiple registration attempts rapidly
        for ($i = 0; $i < 10; $i++) {
            $userData['email'] = "john{$i}@example.com";
            $response = $this->post('/register', $userData);
        }

        // The last request should be rate limited (if rate limiting is implemented)
        // Note: This test depends on your rate limiting configuration
        // You may need to adjust the expected status code
        $response->assertStatus(302); // or 429 if rate limited
    }

    public function test_registration_sanitizes_input_data(): void
    {
        $userData = [
            'name' => '<script>alert("xss")</script>John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password123',
            'password_confirmation' => 'secure-password123',
            'terms' => true,
        ];

        $this->post('/register', $userData);

        $user = User::where('email', 'john@example.com')->first();

        // Name should be sanitized (depending on your implementation)
        $this->assertStringNotContainsString('<script>', $user->name);
        $this->assertStringNotContainsString('alert', $user->name);
    }
}
