<?php

namespace App\Actions\Fortify;

use App\Models\User;
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

        // If user has TOTP 2FA enabled, require 2FA verification
        if ($user->hasEnabledTwoFactorAuthentication()) {
            $rules['two_factor_code'] = ['required', 'string'];
            $messages['two_factor_code.required'] = __('Two-factor authentication code is required when changing password.');
        }

        Validator::make($input, $rules, $messages)->validateWithBag('updatePassword');

        // Verify 2FA if enabled
        if ($user->hasEnabledTwoFactorAuthentication()) {
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

        // Verify TOTP
        try {
            $totpProvider = app(TwoFactorAuthenticationProvider::class);
            $decryptedSecret = decrypt($user->two_factor_secret);
            $isValid = $totpProvider->verify($decryptedSecret, $code);
        } catch (\PragmaRX\Google2FA\Exceptions\InvalidCharactersException $e) {
            // Invalid TOTP secret in database - treat as invalid code
            $isValid = false;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Invalid encrypted secret in database - treat as invalid code
            $isValid = false;
        }

        if (! $isValid) {
            throw ValidationException::withMessages([
                'two_factor_code' => [__('The provided two-factor authentication code is invalid.')],
            ])->errorBag('updatePassword');
        }
    }
}
