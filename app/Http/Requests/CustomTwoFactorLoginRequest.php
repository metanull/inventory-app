<?php

namespace App\Http\Requests;

use App\Services\EmailTwoFactorService;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class CustomTwoFactorLoginRequest extends TwoFactorLoginRequest
{
    /**
     * Determine if the request has a valid two factor code.
     * This extends Fortify's method to also validate email 2FA codes.
     *
     * @return bool
     */
    public function hasValidCode()
    {
        $user = $this->challengedUser();

        Log::info('CustomTwoFactorLoginRequest::hasValidCode called', [
            'user_id' => $user->id,
            'code' => $this->code,
            'has_totp' => $user->hasTotpEnabled(),
            'has_email_2fa' => $user->hasEmailTwoFactorEnabled(),
        ]);

        if (! $this->code) {
            return false;
        }

        // Try TOTP validation if user has TOTP enabled
        if ($user->hasTotpEnabled()) {
            try {
                if (parent::hasValidCode()) {
                    Log::info('TOTP validation succeeded');

                    return true;
                }
            } catch (\Exception $e) {
                Log::info('TOTP validation failed with exception', ['error' => $e->getMessage()]);
            }
        }

        // Try email 2FA validation if user has email 2FA enabled
        if ($user->hasEmailTwoFactorEnabled()) {
            $emailTwoFactorService = app(EmailTwoFactorService::class);

            $isValid = $emailTwoFactorService->verifyCode($user, $this->code);
            Log::info('Email 2FA validation result', ['valid' => $isValid]);

            if ($isValid) {
                $this->session()->forget('login.id');

                return true;
            }
        }

        Log::info('All 2FA validation methods failed');

        return false;
    }
}
