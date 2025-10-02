<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user) {
            // Check email verification first
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // Then check 2FA
            if (($user->hasEmailTwoFactorEnabled() || $user->hasTotpEnabled()) && ! session()->has('auth.password_confirmed_at')) {
                return redirect()->intended(route('two-factor.login'));
            }
        }

        // Default Fortify behavior
        return redirect()->intended(config('fortify.home'));
    }
}
