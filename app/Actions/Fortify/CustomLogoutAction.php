<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;

class CustomLogoutAction
{
    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Clear remember token before logout
        if ($user) {
            $user->setRememberToken(null);
            $user->save();
        }

        // Clear 2FA challenge session
        $request->session()->forget('login.id');

        // Log out the user
        app('auth')->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
