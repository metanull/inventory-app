<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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
        $password = $this->generateSecurePassword();

        $user->forceFill(['password' => Hash::make($password)])->save();

        $user->notify(new class($password) extends \Illuminate\Notifications\Notification {
            public function __construct(private string $plaintext) {}

            public function via(mixed $notifiable): array
            {
                return ['mail'];
            }

            public function toMail(mixed $notifiable): MailMessage
            {
                return (new MailMessage)
                    ->subject('Your new password')
                    ->line('An administrator has generated a new password for your account.')
                    ->line('Your new password is: **'.$this->plaintext.'**')
                    ->line('Please sign in and change your password immediately.');
            }
        });

        return $password;
    }

    private function generateSecurePassword(): string
    {
        $length = 16;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
