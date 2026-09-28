<?php

namespace App\Notifications;

use App\Models\WfhRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WfhRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public WfhRequest $wfhRequest) {}

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
            'kind' => 'wfh_request_submitted',
            'title' => 'New WFH request',
            'message' => sprintf(
                '%s %s submitted a Work From Home request for %s to %s.',
                $this->wfhRequest->employee->first_name,
                $this->wfhRequest->employee->last_name,
                $this->wfhRequest->date_from->format('M j, Y'),
                $this->wfhRequest->date_to->format('M j, Y'),
            ),
            'url' => route('wfh.approval'),
            'wfh_request_id' => $this->wfhRequest->id,
        ];
    }
}
