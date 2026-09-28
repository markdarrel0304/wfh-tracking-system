<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordResetByAdministrator extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'password_reset_by_administrator',
            'title' => 'Password reset by administrator',
            'message' => 'An administrator reset your account password. Use the new password shared with you securely.',
            'url' => route('my-profile'),
        ];
    }
}
