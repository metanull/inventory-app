<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\GeneratedPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPasswordResetService
{
    /**
     * Generate a strong random password, persist the hash, and email the plaintext to the user.
     *
     * @return string The plaintext password (display once to the operator).
     */
    public function generateAndEmail(User $user): string
    {
        $password = Str::password(16, symbols: true);

        $user->forceFill(['password' => Hash::make($password)])->save();

        $user->notify(new GeneratedPasswordNotification($password));

        return $password;
    }
}
