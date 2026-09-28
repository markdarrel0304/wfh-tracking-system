<?php

namespace App\Notifications;

use App\Models\AccomplishmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccomplishmentReportRevisionRequested extends Notification
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
            'kind' => 'accomplishment_report_revision_requested',
            'title' => 'Changes requested for your accomplishment',
            'message' => sprintf(
                'Please update "%s" for %s. Reviewer note: %s',
                $this->accomplishmentReport->title ?? $this->accomplishmentReport->dailyTask?->title ?? 'your accomplishment',
                $this->accomplishmentReport->date->format('M j, Y'),
                $this->accomplishmentReport->review_note,
            ),
            'url' => route('accomplishments.reports', ['date' => $this->accomplishmentReport->date->toDateString(), 'edit' => $this->accomplishmentReport->id]),
            'accomplishment_report_id' => $this->accomplishmentReport->id,
        ];
    }
}
