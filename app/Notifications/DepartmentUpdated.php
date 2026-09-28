<?php

namespace App\Notifications;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepartmentUpdated extends Notification
{
    use Queueable;

    public function __construct(public Department $department) {}

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
            'kind' => 'department_updated',
            'title' => 'Department details updated',
            'message' => "The {$this->department->name} department details have been updated.",
            'url' => route('my-profile'),
            'department_id' => $this->department->id,
        ];
    }
}
