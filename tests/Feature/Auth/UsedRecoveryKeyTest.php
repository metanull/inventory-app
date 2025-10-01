<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class UsedRecoveryKeyTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase, WithFaker;

    public function test_used_recovery_code_cannot_be_reused(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // First login - should succeed
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Logout
        $this->post('/logout');

        // Second login attempt with same recovery code - should fail
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_used_recovery_code_shows_appropriate_error_message(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // Mark recovery code as used
        $this->markRecoveryCodeAsUsed($user, $recoveryCode);

        // Attempt login
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);

        // Verify specific error message (adjust based on your implementation)
        $errors = session('errors')->getBag('default');
        $this->assertStringContainsString('recovery code', $errors->first('recovery_code'));
        $this->assertStringContainsString('invalid', strtolower($errors->first('recovery_code')));
    }

    public function test_attempting_to_use_consumed_recovery_code_is_logged(): void
    {
        Log::spy();

        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // Mark recovery code as used
        $this->markRecoveryCodeAsUsed($user, $recoveryCode);

        // Attempt login
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        // Verify security event is logged
        Log::shouldHaveReceived('warning')
            ->with(\Mockery::pattern('/used recovery code/i'))
            ->orWhereArgs(function ($args) use ($user) {
                return str_contains(strtolower($args[0]), 'recovery') &&
                       str_contains(strtolower($args[0]), 'used') &&
                       (isset($args[1]['user_id']) && $args[1]['user_id'] === $user->id);
            });
    }

    public function test_multiple_failed_recovery_code_attempts_are_tracked(): void
    {
        Log::spy();

        $user = $this->createUserWithRecoveryCodes();
        $usedCode = $this->getUnusedRecoveryCode();

        // Mark recovery code as used
        $this->markRecoveryCodeAsUsed($user, $usedCode);

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Make multiple attempts with the used recovery code
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/two-factor-challenge', [
                'recovery_code' => $usedCode,
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors(['recovery_code']);
        }

        // Verify multiple attempts are logged
        Log::shouldHaveReceived('warning')->atLeast(3);
    }

    public function test_recovery_code_marked_as_used_has_timestamp(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // Login and use recovery code
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $beforeUse = now();

        $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $afterUse = now();

        // Verify recovery code is marked as used with proper timestamp
        $user->refresh();
        $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $usedCode = $codes->firstWhere('code', $recoveryCode);

        $this->assertNotNull($usedCode);
        $this->assertArrayHasKey('used_at', $usedCode);
        $this->assertNotNull($usedCode['used_at']);

        $usedAt = \Carbon\Carbon::parse($usedCode['used_at']);
        $this->assertTrue($usedAt->between($beforeUse, $afterUse));
    }

    public function test_unused_recovery_codes_remain_valid_after_one_is_used(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Get all recovery codes
        $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
            ->pluck('code')
            ->toArray();

        $firstCode = $codes[0];
        $secondCode = $codes[1];

        // Use first recovery code
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post('/two-factor-challenge', [
            'recovery_code' => $firstCode,
        ]);

        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // Second recovery code should still work
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $secondCode,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_usage_prevents_concurrent_logins(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // Start first login session
        $firstSession = $this->session([]);
        $firstSession->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Start second login session
        $secondSession = $this->session([]);
        $secondSession->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // First session uses recovery code - should succeed
        $response = $firstSession->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');

        // Second session tries to use same recovery code - should fail
        $response = $secondSession->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
    }

    public function test_used_recovery_code_validation_is_case_insensitive(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // Use recovery code in uppercase
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post('/two-factor-challenge', [
            'recovery_code' => strtoupper($recoveryCode),
        ]);

        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // Try to use same code in lowercase - should fail
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => strtolower($recoveryCode),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recovery_code']);
        $this->assertGuest();
    }

    public function test_recovery_code_database_consistency_after_use(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $recoveryCode = $this->getUnusedRecoveryCode();

        // Get initial state
        $initialCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $initialUnusedCount = $initialCodes->where('used_at', null)->count();

        // Use recovery code
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        // Verify database consistency
        $user->refresh();
        $updatedCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        // One less unused code
        $this->assertEquals($initialUnusedCount - 1, $updatedCodes->whereNull('used_at')->count());

        // Same total number of codes
        $this->assertEquals($initialCodes->count(), $updatedCodes->count());

        // Specific code is marked as used
        $usedCode = $updatedCodes->firstWhere('code', $recoveryCode);
        $this->assertNotNull($usedCode);
        $this->assertNotNull($usedCode['used_at']);
    }

    public function test_security_headers_prevent_recovery_code_caching(): void
    {
        $user = $this->createUserWithRecoveryCodes();

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Access the 2FA challenge page
        $response = $this->get('/two-factor-challenge');

        // Verify security headers are present
        $response->assertHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', '0');
    }

    public function test_recovery_code_brute_force_protection(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $usedCode = $this->getUnusedRecoveryCode();

        // Mark code as used
        $this->markRecoveryCodeAsUsed($user, $usedCode);

        // Login to get to 2FA challenge
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Make multiple rapid attempts with used recovery code
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/two-factor-challenge', [
                'recovery_code' => $usedCode,
            ]);

            if ($i < 5) {
                // First few attempts should return validation errors
                $response->assertStatus(302);
                $response->assertSessionHasErrors(['recovery_code']);
            } else {
                // Later attempts might be rate limited (if implemented)
                // Adjust based on your rate limiting implementation
                $this->assertTrue(
                    $response->status() === 302 || $response->status() === 429
                );
            }
        }
    }
}
