<?php

namespace App\Notifications;

use App\Models\AccomplishmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccomplishmentReportReviewed extends Notification
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
            'kind' => 'accomplishment_report_reviewed',
            'title' => 'Accomplishment reviewed',
            'message' => sprintf(
                'Your accomplishment "%s" for %s has been reviewed.',
                $this->accomplishmentReport->title ?? $this->accomplishmentReport->dailyTask?->title ?? 'report',
                $this->accomplishmentReport->date->format('M j, Y'),
            ),
            'url' => route('accomplishments.reports', ['date' => $this->accomplishmentReport->date->toDateString(), 'edit' => $this->accomplishmentReport->id]),
            'accomplishment_report_id' => $this->accomplishmentReport->id,
        ];
    }
}
