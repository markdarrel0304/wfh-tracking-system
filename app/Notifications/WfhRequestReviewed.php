<?php

namespace App\Notifications;

use App\Models\WfhRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WfhRequestReviewed extends Notification
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
        $isApproved = $this->wfhRequest->status === 'approved';

        return [
            'kind' => 'wfh_request_reviewed',
            'title' => $isApproved ? 'WFH request approved' : 'WFH request not approved',
            'message' => sprintf(
                'Your Work From Home request for %s to %s was %s.',
                $this->wfhRequest->date_from->format('M j, Y'),
                $this->wfhRequest->date_to->format('M j, Y'),
                $isApproved ? 'approved' : 'not approved',
            ),
            'url' => route('wfh.requests.show', $this->wfhRequest),
            'wfh_request_id' => $this->wfhRequest->id,
        ];
    }
}
