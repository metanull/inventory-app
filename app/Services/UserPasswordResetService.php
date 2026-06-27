<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;

class UserPasswordResetService
{
    /**
     * Send a Filament-native password reset link to the user.
     * No plaintext password is generated or exposed to the administrator.
     */
    public function sendResetLink(User $user): void
    {
        $token = Password::broker(Config::string('fortify.passwords'))->createToken($user);
        $user->notify(new AdminPasswordResetNotification($token));
    }
}
