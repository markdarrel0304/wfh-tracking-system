<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountSettingsUpdated extends Notification
{
    use Queueable;

    /** @param array<int, string> $changedSettings */
    public function __construct(public User $user, public array $changedSettings) {}

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
            'kind' => 'account_settings_updated',
            'title' => 'Account settings updated',
            'message' => 'An administrator updated your '.implode(', ', $this->changedSettings).'.',
            'url' => route('my-profile'),
            'user_id' => $this->user->id,
        ];
    }
}
