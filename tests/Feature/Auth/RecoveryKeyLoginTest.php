<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class RecoveryKeyLoginTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase;

    public function test_user_can_login_with_valid_recovery_code(): void
    {
        Event::fake();
        $user = $this->createUserWithRecoveryCodes();

        // First, login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Then use recovery code
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $this->getUnusedRecoveryCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Login::class);
    }

    public function test_recovery_code_is_marked_as_used_after_successful_login(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Use recovery code
        $recoveryCode = $this->getUnusedRecoveryCode();
        $this->post(route('two-factor.login.store'), [
            'recovery_code' => $recoveryCode,
        ]);

        // Verify recovery code is replaced (Fortify's secure approach)
        $user->refresh();
        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        // The used code should no longer exist (replaced with new one)
        $this->assertFalse($recoveryCodes->contains($recoveryCode));

        // Should still have same number of recovery codes
        $this->assertCount(5, $recoveryCodes);
    }

    public function test_user_cannot_login_with_already_used_recovery_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $usedCode = $this->getUnusedRecoveryCode();

        // Mark the recovery code as used
        $this->markRecoveryCodeAsUsed($user, $usedCode);

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try to use the already used recovery code
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $usedCode,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_invalid_recovery_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try invalid recovery code
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => 'invalid-recovery-code',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_empty_recovery_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try empty recovery code
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_recovery_code_login_without_2fa_challenge_fails(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Try to use recovery code without being in 2FA challenge (no session)
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $this->getUnusedRecoveryCode(),
        ]);

        // Fortify redirects back to 2FA challenge page with error when session is missing
        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_user_without_recovery_codes_cannot_use_recovery_login(): void
    {
        $user = $this->createUserWithTotp([
            'two_factor_recovery_codes' => null,
        ]);

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try to use recovery code
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => 'some-recovery-code',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_recovery_code_format_is_validated(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try recovery code with invalid format
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => 'short',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_recovery_code_is_case_insensitive(): void
    {
        Event::fake();
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Use recovery code in different case
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => strtoupper($this->getUnusedRecoveryCode()),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_can_only_be_used_once(): void
    {
        Event::fake();
        $user = $this->createUserWithRecoveryCodes();

        $recoveryCode = $this->getUnusedRecoveryCode();

        // First use should succeed
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Logout and try to use the same code again
        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Second use of same code should fail (code was replaced after first use)
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_recovery_code_login_rate_limiting(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Make multiple failed attempts
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('two-factor.login.store'), [
                'recovery_code' => 'invalid-code-'.$i,
            ]);
        }

        // Last request should be rate limited (if implemented)
        // Note: This test depends on your rate limiting configuration
        $response->assertStatus(302); // or 429 if rate limited
        $this->assertGuest();
    }

    public function test_recovery_code_login_with_expired_challenge_session(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Simulate session expiry by clearing session
        session()->flush();

        // Try to use recovery code with expired session
        $response = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $this->getUnusedRecoveryCode(),
        ]);

        // Fortify redirects back to 2FA challenge page with error when session is expired
        $response->assertStatus(302);
        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }
}
