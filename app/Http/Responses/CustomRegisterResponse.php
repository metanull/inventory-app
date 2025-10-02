<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class CustomRegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user) {
            // Check email verification first
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // Then check 2FA (though newly registered users typically won't have 2FA enabled yet)
            if (($user->hasEmailTwoFactorEnabled() || $user->hasTotpEnabled()) && ! session()->has('auth.password_confirmed_at')) {
                return redirect()->intended(route('two-factor.login'));
            }
        }

        // Default redirect to dashboard
        return redirect()->intended(config('fortify.home'));
    }
}
