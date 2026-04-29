<?php

namespace App\Notifications\Filament\Auth;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailTwoFactorCodeNotification extends Notification
{
    public function __construct(private readonly string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Inventory App admin sign-in code')
            ->line('You are receiving this message because a sign-in attempt to the Inventory App admin panel requires two-factor verification.')
            ->line('Your verification code is: **'.$this->code.'**')
            ->line('This code will expire in **10 minutes**.')
            ->line('If you did not attempt to sign in, please ignore this message.');
    }
}
