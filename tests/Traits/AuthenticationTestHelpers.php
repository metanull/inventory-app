<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

trait AuthenticationTestHelpers
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
     */
    protected function createUserWithTotp(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'two_factor_secret' => encrypt('test-secret'),
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
     * Mock the TOTP provider to return the specified result.
     */
    protected function mockTotpProvider(bool $verificationResult = true): void
    {
        $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) use ($verificationResult) {
            $mock->shouldReceive('verify')
                ->andReturn($verificationResult);
        });
    }

    /**
     * Get valid test TOTP code.
     */
    protected function getValidTotpCode(): string
    {
        return '123456';
    }

    /**
     * Get invalid test TOTP code.
     */
    protected function getInvalidTotpCode(): string
    {
        return '000000';
    }

    /**
     * Get unused recovery code.
     */
    protected function getUnusedRecoveryCode(): string
    {
        return 'RECOVERY-CODE-1';
    }

    /**
     * Get used recovery code.
     */
    protected function getUsedRecoveryCode(): string
    {
        return 'used-recovery-code';
    }

    /**
     * Simulate using a recovery code by replacing it (following Fortify's secure approach).
     */
    protected function markRecoveryCodeAsUsed(User $user, string $code): void
    {
        // Use Fortify's standard replacement approach for testing
        $user->replaceRecoveryCode($code);
    }
}
