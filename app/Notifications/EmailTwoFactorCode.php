<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailTwoFactorCode extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The two-factor authentication code.
     */
    public string $code;

    /**
     * The expiry time in minutes.
     */
    public int $expiryMinutes;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code, int $expiryMinutes = 5)
    {
        $this->code = $code;
        $this->expiryMinutes = $expiryMinutes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Two-Factor Authentication Code')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('You are receiving this email because a two-factor authentication code was requested for your account.')
            ->line('Your verification code is:')
            ->line('**'.$this->code.'**')
            ->line('This code will expire in '.$this->expiryMinutes.' minutes.')
            ->line('If you did not request this code, please ignore this email and consider changing your password.')
            ->line('For security reasons, please do not share this code with anyone.')
            ->salutation('Regards, '.config('app.name').' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,      // Store for logging/debugging (without exposing in UI)
            'expiry_minutes' => $this->expiryMinutes,
            'sent_at' => now()->toISOString(),
        ];
    }
}
