<?php

namespace App\Notifications;

use App\Models\AccomplishmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccomplishmentReportSubmitted extends Notification
{
    use Queueable;

    public function __construct(public AccomplishmentReport $accomplishmentReport) {}

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
            'kind' => 'accomplishment_report_submitted',
            'title' => 'New accomplishment to review',
            'message' => sprintf(
                '%s %s submitted "%s" for %s.',
                $this->accomplishmentReport->employee->first_name,
                $this->accomplishmentReport->employee->last_name,
                $this->accomplishmentReport->title ?? $this->accomplishmentReport->dailyTask?->title ?? 'an accomplishment',
                $this->accomplishmentReport->date->format('M j, Y'),
            ),
            'url' => route('approvals.accomplishment-reports'),
            'accomplishment_report_id' => $this->accomplishmentReport->id,
        ];
    }
}
