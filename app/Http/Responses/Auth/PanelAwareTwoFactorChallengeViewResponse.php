<?php

namespace App\Http\Responses\Auth;

use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;

class PanelAwareTwoFactorChallengeViewResponse implements TwoFactorChallengeViewResponse
{
    public function toResponse($request)
    {
        if ($request->session()->get('filament.auth.panel') === 'admin') {
            return redirect()->route('filament.admin.auth.two-factor-challenge');
        }

        return view('auth.two-factor-challenge');
    }
}
