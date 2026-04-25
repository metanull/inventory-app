<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneratedPasswordNotification extends Notification
{
    public function __construct(private readonly string $plaintext) {}

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
}
