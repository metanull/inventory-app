<?php

namespace App\Actions\Fortify;

use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class CustomLoginResponse implements LoginResponseContract, TwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();
        $filamentPanelId = $request->session()->pull('filament.auth.panel');

        if ($user) {
            // Check email verification first
            if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            if (filled($filamentPanelId)) {
                $panel = Filament::getPanel($filamentPanelId, isStrict: false);

                if ($panel) {
                    return redirect()->to($panel->getUrl());
                }
            }
        }

        // Default Fortify behavior
        return redirect()->intended(config('fortify.home'));
    }
}
