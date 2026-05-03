<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class AdminPasswordResetNotification extends ResetPassword
{
    /**
     * Get the password reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable): string
    {
        return route('filament.admin.auth.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
