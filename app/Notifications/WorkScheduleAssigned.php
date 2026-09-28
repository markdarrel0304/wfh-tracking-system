<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkScheduleAssigned extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ?string $scheduleName,
        public ?string $effectiveDate = null,
        public bool $returnsToDefault = false,
    ) {}

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
        $effectiveDateNote = $this->effectiveDate ? " starting {$this->effectiveDate}" : '';

        return [
            'kind' => $this->returnsToDefault ? 'work_schedule_reverted' : 'work_schedule_assigned',
            'title' => $this->returnsToDefault ? 'Schedule returned to default' : 'Work schedule updated',
            'message' => $this->returnsToDefault
                ? "Your temporary schedule will end{$effectiveDateNote}. Your default schedule will apply again."
                : ($this->scheduleName
                    ? "Your assigned schedule is {$this->scheduleName}{$effectiveDateNote}. Open My Schedule to review your working days and hours."
                    : 'Your schedule assignment has been removed. Contact your administrator for your new schedule.'),
            'url' => route('my-work-schedule'),
        ];
    }
}
