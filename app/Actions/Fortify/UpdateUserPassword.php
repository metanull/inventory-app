<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\EmailTwoFactorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $rules = [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ];

        $messages = [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ];

        // If user has any form of 2FA enabled, require 2FA verification
        if ($user->hasAnyTwoFactorEnabled()) {
            $rules['two_factor_code'] = ['required', 'string'];
            $messages['two_factor_code.required'] = __('Two-factor authentication code is required when changing password.');
        }

        Validator::make($input, $rules, $messages)->validateWithBag('updatePassword');

        // Verify 2FA if enabled
        if ($user->hasAnyTwoFactorEnabled()) {
            $this->verifyTwoFactorCode($user, $input['two_factor_code']);
        }

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }

    /**
     * Verify the provided two-factor authentication code.
     */
    protected function verifyTwoFactorCode(User $user, string $code): void
    {
        $isValid = false;

        // Try TOTP first if enabled
        if ($user->hasEnabledTwoFactorAuthentication()) {
            try {
                $totpProvider = app(TwoFactorAuthenticationProvider::class);
                $isValid = $totpProvider->verify($user, $code);
            } catch (\PragmaRX\Google2FA\Exceptions\InvalidCharactersException $e) {
                // Invalid TOTP secret in database - treat as invalid code
                $isValid = false;
            }
        }

        // Try email 2FA if TOTP failed and email 2FA is enabled
        if (! $isValid && $user->email_2fa_enabled) {
            $emailTwoFactorService = app(EmailTwoFactorService::class);
            $isValid = $emailTwoFactorService->verifyCode($user, $code);
        }

        if (! $isValid) {
            throw ValidationException::withMessages([
                'two_factor_code' => [__('The provided two-factor authentication code is invalid.')],
            ])->errorBag('updatePassword');
        }
    }
}
