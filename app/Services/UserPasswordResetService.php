<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use Illuminate\Auth\Passwords\PasswordBroker;
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
        // Password::broker() is declared as returning the CONTRACT, which since
        // laravel/framework v12.68.0 no longer lists createToken()/deleteToken()/
        // getRepository() — those live on the concrete broker the manager
        // actually builds. Narrowing here rather than suppressing the analyser.
        /** @var PasswordBroker $broker */
        $broker = Password::broker(Config::string('fortify.passwords'));

        $token = $broker->createToken($user);
        $user->notify(new AdminPasswordResetNotification($token));
    }
}
