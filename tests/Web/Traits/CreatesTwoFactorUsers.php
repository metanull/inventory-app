<?php

namespace Tests\Web\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Mockery;
use PragmaRX\Google2FA\Google2FA;

/**
 * Provides helper methods for creating users with different 2FA configurations
 */
trait CreatesTwoFactorUsers
{
    /**
     * Create a user without any 2FA enabled.
     */
    protected function createUserWithoutTwoFactor(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ], $attributes));
    }

    /**
     * Create a user with TOTP enabled.
     * Uses a valid Base32 secret for testing.
     */
    protected function createUserWithTotp(array $attributes = []): User
    {
        // Valid Base32 secret for testing (160-bit secret)
        $secret = 'JBSWY3DPEHPK3PXP'; // "Hello" in Base32

        return User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ], $attributes));
    }

    /**
     * Create a user with recovery codes.
     */
    protected function createUserWithRecoveryCodes(array $attributes = []): User
    {
        $user = $this->createUserWithTotp($attributes);

        $recoveryCodes = [
            'RECOVERY-CODE-1',
            'RECOVERY-CODE-2',
            'RECOVERY-CODE-3',
            'RECOVERY-CODE-4',
            'RECOVERY-CODE-5',
        ];

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ])->save();

        return $user;
    }

    /**
     * Mock the TOTP provider to return a specific verification result.
     */
    protected function mockTotpProvider(bool $shouldVerify = true): void
    {
        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')
            ->andReturn($shouldVerify);

        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);
    }

    /**
     * Get a valid TOTP code for testing (uses a fixed secret).
     */
    protected function getValidTotpCode(): string
    {
        $google2fa = new Google2FA;

        // Same secret as used in createUserWithTotp()
        return $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');
    }

    /**
     * Get an invalid TOTP code for testing.
     */
    protected function getInvalidTotpCode(): string
    {
        return '000000'; // Always invalid
    }

    /**
     * Get an unused recovery code from the generated codes.
     */
    protected function getUnusedRecoveryCode(): string
    {
        return 'RECOVERY-CODE-1';
    }

    /**
     * Mark a recovery code as used for a given user.
     * In reality, Fortify replaces used codes with new ones, but for testing
     * we just remove the used code to simulate it being unavailable.
     */
    protected function markRecoveryCodeAsUsed(User $user, string $code): void
    {
        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        // Remove the used code (simulating Fortify's replacement behavior)
        $recoveryCodes = $recoveryCodes->reject(fn ($recoveryCode) => $recoveryCode === $code);

        // Add a new replacement code to maintain count
        $recoveryCodes->push('REPLACEMENT-CODE-'.Str::random(8));

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt($recoveryCodes->values()->toJson()),
        ])->save();
    }
}
