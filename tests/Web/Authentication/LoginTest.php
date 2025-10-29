<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Web\Traits\CreatesTwoFactorUsers;

class LoginTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    public function test_user_can_login_without_two_factor(): void
    {
        Event::fake();
        $user = $this->createUserWithoutTwoFactor();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Login::class);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = $this->createUserWithoutTwoFactor();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post(route('login.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_user_with_totp_is_redirected_to_two_factor_challenge(): void
    {
        $user = $this->createUserWithTotp();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest(); // Should not be authenticated yet
    }

    public function test_user_can_complete_totp_challenge(): void
    {
        Event::fake();
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp();

        // First, login to get to the 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Then complete the 2FA challenge
        $response = $this->post(route('two-factor.login.store'), [
            'code' => $this->getValidTotpCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Login::class);
    }

    public function test_totp_challenge_fails_with_invalid_code(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUserWithTotp();

        // First, login to get to the 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Then try to complete the 2FA challenge with invalid code
        $response = $this->post(route('two-factor.login.store'), [
            'code' => $this->getInvalidTotpCode(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['code']);
        $this->assertGuest();
    }

    public function test_two_factor_challenge_expires_after_timeout(): void
    {
        $user = $this->createUserWithTotp();

        // Attempt 2FA challenge without first logging in (no session)
        $response = $this->post(route('two-factor.login.store'), [
            'code' => $this->getValidTotpCode(),
        ]);

        // Fortify redirects back to 2FA challenge page with error when session is missing
        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHasErrors(['code']);
        $this->assertGuest();
    }

    public function test_user_can_access_two_factor_challenge_page(): void
    {
        $user = $this->createUserWithTotp();

        // First login to establish 2FA challenge session
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Then access the challenge page
        $response = $this->get(route('two-factor.login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.two-factor-challenge');
    }

    public function test_two_factor_challenge_page_redirects_when_not_in_challenge(): void
    {
        $response = $this->get(route('two-factor.login'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_cannot_access_login_page(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->get(route('login'));

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_handles_invalid_base32_totp_secret_gracefully(): void
    {
        // Create user with invalid Base32 TOTP secret
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'two_factor_secret' => encrypt('INVALID0TOTP1SECRET'), // Contains invalid Base32 chars
            'two_factor_confirmed_at' => now(),
        ]);

        // First login should work (gets to 2FA challenge)
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));

        // 2FA challenge should fail gracefully, not crash
        $response = $this->post(route('two-factor.login.store'), [
            'code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['code']);
        $this->assertGuest();
    }

    public function test_remember_me_functionality_works(): void
    {
        $user = $this->createUserWithoutTwoFactor();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Check that remember token was set
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_login_with_email_verification_required(): void
    {
        $user = $this->createUserWithoutTwoFactor([
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect to email verification page
        $response->assertStatus(302);
        $response->assertRedirect(route('verification.notice'));
        // User is logged in but needs to verify email
        $this->assertAuthenticatedAs($user);
    }
}
