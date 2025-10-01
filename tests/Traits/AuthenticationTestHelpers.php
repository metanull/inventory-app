<?php

namespace Tests\Traits;

use App\Models\User;
use App\Services\EmailTwoFactorService;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;

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
            'email_2fa_enabled' => false,
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
            'email_2fa_enabled' => false,
        ], $attributes));
    }

    /**
     * Create a user with email 2FA enabled.
     */
    protected function createUserWithEmailTwoFactor(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'email_2fa_enabled' => true,
        ], $attributes));
    }

    /**
     * Create a user with both TOTP and email 2FA enabled.
     */
    protected function createUserWithBothTwoFactor(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'email_2fa_enabled' => true,
        ], $attributes));
    }

    /**
     * Create a user with recovery codes.
     */
    protected function createUserWithRecoveryCodes(array $attributes = []): User
    {
        $user = $this->createUserWithTotp($attributes);

        $recoveryCodes = collect([
            'recovery-code-1',
            'recovery-code-2',
            'recovery-code-3',
            'recovery-code-4',
            'recovery-code-5',
        ])->map(function ($code) {
            return new RecoveryCode($code);
        })->toArray();

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
     * Mock the email 2FA service to return the specified result.
     */
    protected function mockEmailTwoFactorService(bool $verificationResult = true): void
    {
        $this->mock(EmailTwoFactorService::class, function ($mock) use ($verificationResult) {
            $mock->shouldReceive('verifyCode')
                ->andReturn($verificationResult);

            $mock->shouldReceive('sendVerificationCode')
                ->andReturn(true);
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
     * Get valid test email 2FA code.
     */
    protected function getValidEmailTwoFactorCode(): string
    {
        return '654321';
    }

    /**
     * Get invalid test email 2FA code.
     */
    protected function getInvalidEmailTwoFactorCode(): string
    {
        return '111111';
    }

    /**
     * Get unused recovery code.
     */
    protected function getUnusedRecoveryCode(): string
    {
        return 'recovery-code-1';
    }

    /**
     * Get used recovery code.
     */
    protected function getUsedRecoveryCode(): string
    {
        return 'used-recovery-code';
    }

    /**
     * Mark a recovery code as used for a user.
     */
    protected function markRecoveryCodeAsUsed(User $user, string $code): void
    {
        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
            ->map(function ($recoveryCode) use ($code) {
                if ($recoveryCode['code'] === $code) {
                    $recoveryCode['used_at'] = now()->toISOString();
                }

                return $recoveryCode;
            });

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt($recoveryCodes->toJson()),
        ])->save();
    }
}
