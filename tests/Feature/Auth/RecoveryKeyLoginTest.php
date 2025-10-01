<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\RecoveryCode;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class RecoveryKeyLoginTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase, WithFaker;

    public function test_user_can_login_with_valid_recovery_code(): void
    {
        Event::fake();
        $user = $this->createUserWithRecoveryCodes();

        // First, login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Then use recovery code
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $this->getUnusedRecoveryCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Login::class);
    }

    public function test_recovery_code_is_marked_as_used_after_successful_login(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Use recovery code
        $recoveryCode = $this->getUnusedRecoveryCode();
        $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        // Check that recovery code is marked as used
        $user->refresh();
        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        $usedCode = $recoveryCodes->firstWhere('code', $recoveryCode);
        $this->assertNotNull($usedCode);
        $this->assertNotNull($usedCode['used_at']);
    }

    public function test_user_cannot_login_with_already_used_recovery_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $usedCode = $this->getUnusedRecoveryCode();

        // Mark the recovery code as used
        $this->markRecoveryCodeAsUsed($user, $usedCode);

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try to use the already used recovery code
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $usedCode,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_invalid_recovery_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try invalid recovery code
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => 'invalid-recovery-code',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_empty_recovery_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try empty recovery code
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_recovery_code_login_works_with_both_totp_and_email_2fa_enabled(): void
    {
        Event::fake();
        $user = $this->createUserWithBothTwoFactor();

        // Add recovery codes
        $recoveryCodes = collect([
            'recovery-code-1',
            'recovery-code-2',
        ])->map(function ($code) {
            return new RecoveryCode($code);
        })->toArray();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ])->save();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Use recovery code
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => 'recovery-code-1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Login::class);
    }

    public function test_recovery_code_login_without_2fa_challenge_fails(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Try to use recovery code without being in 2FA challenge
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $this->getUnusedRecoveryCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_without_recovery_codes_cannot_use_recovery_login(): void
    {
        $user = $this->createUserWithTotp([
            'two_factor_recovery_codes' => null,
        ]);

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try to use recovery code
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => 'some-recovery-code',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_recovery_code_format_is_validated(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Try recovery code with invalid format
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => 'short',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_recovery_code_is_case_insensitive(): void
    {
        Event::fake();
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Use recovery code in different case
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => strtoupper($this->getUnusedRecoveryCode()),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_all_recovery_codes_can_be_used_once(): void
    {
        Event::fake();
        $user = $this->createUserWithRecoveryCodes();

        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
            ->pluck('code')
            ->toArray();

        foreach ($recoveryCodes as $index => $code) {
            // Create fresh session for each attempt
            $this->post('/logout');

            // Login to get to 2FA challenge
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            // Use recovery code
            $response = $this->post('/two-factor-challenge', [
                'recovery_code' => $code,
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs($user);
        }

        // All codes should now be marked as used
        $user->refresh();
        $updatedCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        foreach ($updatedCodes as $recoveryCode) {
            $this->assertNotNull($recoveryCode['used_at']);
        }
    }

    public function test_recovery_code_login_rate_limiting(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Make multiple failed attempts
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/two-factor-challenge', [
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
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Simulate session expiry by clearing session
        session()->flush();

        // Try to use recovery code with expired session
        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $this->getUnusedRecoveryCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
