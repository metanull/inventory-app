<?php

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

// TODO: This file should be deleted - it provides no custom functionality
// It is no longer registered in FortifyServiceProvider
// Fortify's default FailedTwoFactorLoginResponse should be used

class CustomFailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * This just replicates Fortify's default behavior - no customization
     */
    public function toResponse($request)
    {
        [$key, $message] = $request->filled('recovery_code')
            ? ['recovery_code', __('The provided two factor recovery code was invalid.')]
            : ['code', __('The provided two factor authentication code was invalid.')];

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                $key => [$message],
            ]);
        }

        return redirect()->route('two-factor.login')->withErrors([$key => $message]);
    }
}
