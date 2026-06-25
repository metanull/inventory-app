<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword;

class AdminPasswordResetNotification extends ResetPassword
{
    /**
     * Get the password reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable): string
    {
        $email = $notifiable instanceof CanResetPassword
            ? $notifiable->getEmailForPasswordReset()
            : '';

        return route('filament.admin.auth.password.reset', [
            'token' => $this->token,
            'email' => $email,
        ]);
    }
}
