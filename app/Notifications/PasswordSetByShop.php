<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells someone their password was changed by the shop, so a change they
 * didn't expect doesn't go unnoticed. Never includes the password.
 */
class PasswordSetByShop extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $changedBy) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Yande Gadgets password was changed')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->changedBy} at the shop has set a new password for your account, and you've been signed out on your other devices.")
            ->line('They\'ll give you the new password. Once you\'re signed in, you can change it to one only you know under Settings → Password.')
            ->line('If you didn\'t expect this, please contact the shop.')
            ->action('Sign in', route('login'));
    }
}
