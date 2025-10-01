<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class RecoveryKeyGenerationTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase, WithFaker;

    public function test_user_can_generate_recovery_codes_when_totp_enabled(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        // Verify recovery codes were generated
        $user->refresh();
        $this->assertNotNull($user->two_factor_recovery_codes);

        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $this->assertCount(8, $recoveryCodes); // Laravel Jetstream default

        // Verify each recovery code has proper structure
        foreach ($recoveryCodes as $recoveryCode) {
            $this->assertArrayHasKey('code', $recoveryCode);
            $this->assertArrayNotHasKey('used_at', $recoveryCode);
            $this->assertIsString($recoveryCode['code']);
            $this->assertGreaterThan(10, strlen($recoveryCode['code'])); // Minimum length check
        }
    }

    public function test_user_cannot_generate_recovery_codes_without_totp(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(422); // Or redirect with error

        // Verify no recovery codes were generated
        $user->refresh();
        $this->assertNull($user->two_factor_recovery_codes);
    }

    public function test_generating_new_recovery_codes_replaces_existing_ones(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $this->actingAs($user);

        // Get original recovery codes
        $originalCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
            ->pluck('code')
            ->toArray();

        // Generate new recovery codes
        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        // Verify new codes are different
        $user->refresh();
        $newCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
            ->pluck('code')
            ->toArray();

        $this->assertNotEquals($originalCodes, $newCodes);
        $this->assertCount(8, $newCodes);
    }

    public function test_used_recovery_codes_are_invalidated_when_generating_new_ones(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $this->actingAs($user);

        // Mark a recovery code as used
        $usedCode = $this->getUnusedRecoveryCode();
        $this->markRecoveryCodeAsUsed($user, $usedCode);

        // Verify the code is marked as used
        $user->refresh();
        $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $usedCodeEntry = $codes->firstWhere('code', $usedCode);
        $this->assertNotNull($usedCodeEntry['used_at']);

        // Generate new recovery codes
        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        // Verify all new codes are unused
        $user->refresh();
        $newCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        foreach ($newCodes as $recoveryCode) {
            $this->assertArrayNotHasKey('used_at', $recoveryCode);
        }

        // Verify the old used code is no longer present
        $this->assertNull($newCodes->firstWhere('code', $usedCode));
    }

    public function test_recovery_codes_are_unique_across_generations(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        $allGeneratedCodes = [];

        // Generate recovery codes multiple times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/user/two-factor-recovery-codes');
            $response->assertStatus(200);

            $user->refresh();
            $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
                ->pluck('code')
                ->toArray();

            // Check for duplicates within this generation
            $this->assertEquals(count($codes), count(array_unique($codes)));

            // Check for duplicates across generations
            foreach ($codes as $code) {
                $this->assertNotContains($code, $allGeneratedCodes);
                $allGeneratedCodes[] = $code;
            }
        }
    }

    public function test_guest_cannot_generate_recovery_codes(): void
    {
        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_recovery_codes_generation_requires_current_password(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        // If your implementation requires current password for security
        $response = $this->post('/user/two-factor-recovery-codes', [
            'current_password' => 'wrong-password',
        ]);

        // This test depends on your implementation
        // Adjust based on whether you require password confirmation
        $response->assertStatus(422); // or success if no password required
    }

    public function test_recovery_codes_are_properly_encrypted(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        $user->refresh();

        // Raw database value should be encrypted
        $rawValue = $user->getAttributes()['two_factor_recovery_codes'];
        $this->assertIsString($rawValue);
        $this->assertStringNotContainsString('recovery-code', $rawValue); // Should not contain plain text

        // Decrypted value should be valid JSON
        $decrypted = decrypt($user->two_factor_recovery_codes);
        $codes = json_decode($decrypted, true);
        $this->assertIsArray($codes);
        $this->assertGreaterThan(0, count($codes));
    }

    public function test_recovery_codes_have_proper_format(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        $user->refresh();
        $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        foreach ($codes as $recoveryCode) {
            // Verify structure
            $this->assertIsArray($recoveryCode);
            $this->assertArrayHasKey('code', $recoveryCode);

            // Verify code format (typically alphanumeric, specific length)
            $code = $recoveryCode['code'];
            $this->assertIsString($code);
            $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/i', $code); // Alphanumeric with dashes
            $this->assertGreaterThanOrEqual(10, strlen($code)); // Minimum length
            $this->assertLessThanOrEqual(50, strlen($code)); // Maximum length
        }
    }

    public function test_recovery_codes_generation_is_logged(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        // Clear any existing logs
        \Illuminate\Support\Facades\Log::spy();

        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        // Verify security event is logged (if implemented)
        // This depends on your logging implementation
        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')
            ->with(\Mockery::pattern('/recovery codes generated/i'))
            ->orWhereArgs(function ($args) {
                return str_contains(strtolower($args[0]), 'recovery') &&
                       str_contains(strtolower($args[0]), 'generated');
            });
    }

    public function test_recovery_codes_display_page_shows_new_codes(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        // Generate recovery codes
        $this->post('/user/two-factor-recovery-codes');

        // View the recovery codes page
        $response = $this->get('/user/two-factor-recovery-codes');

        $response->assertStatus(200);
        $response->assertViewIs('profile.show-recovery-codes'); // Adjust view name as needed

        $user->refresh();
        $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        // Verify codes are displayed
        foreach ($codes as $recoveryCode) {
            $response->assertSeeText($recoveryCode['code']);
        }
    }

    public function test_recovery_codes_can_be_regenerated_multiple_times(): void
    {
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        $generatedCodeSets = [];

        // Generate recovery codes multiple times
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/user/two-factor-recovery-codes');
            $response->assertStatus(200);

            $user->refresh();
            $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
                ->pluck('code')
                ->sort()
                ->values()
                ->toArray();

            $generatedCodeSets[] = $codes;
        }

        // Verify each set is different
        $this->assertNotEquals($generatedCodeSets[0], $generatedCodeSets[1]);
        $this->assertNotEquals($generatedCodeSets[1], $generatedCodeSets[2]);
        $this->assertNotEquals($generatedCodeSets[0], $generatedCodeSets[2]);
    }

    public function test_recovery_codes_generation_with_email_2fa_enabled(): void
    {
        $user = $this->createUserWithBothTwoFactor();
        $this->actingAs($user);

        $response = $this->post('/user/two-factor-recovery-codes');

        $response->assertStatus(200);

        // Recovery codes should be generated even with email 2FA
        $user->refresh();
        $this->assertNotNull($user->two_factor_recovery_codes);

        $codes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $this->assertGreaterThan(0, $codes->count());
    }
}
