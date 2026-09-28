<?php

namespace App\Notifications;

use App\Models\Holiday;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HolidayScheduleUpdated extends Notification
{
    use Queueable;

    public function __construct(public Holiday $holiday, public bool $wasCreated) {}

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
            'kind' => 'holiday_schedule_updated',
            'title' => $this->wasCreated ? 'Holiday added to your schedule' : 'Holiday schedule updated',
            'message' => $this->holiday->is_active
                ? "{$this->holiday->name} is scheduled for {$this->holiday->date->format('M j, Y')}."
                : "{$this->holiday->name} is no longer active on work schedules.",
            'url' => route('my-work-schedule'),
            'holiday_id' => $this->holiday->id,
        ];
    }
}
