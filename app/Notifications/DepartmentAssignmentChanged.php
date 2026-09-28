<?php

namespace App\Notifications;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepartmentAssignmentChanged extends Notification
{
    use Queueable;

    public function __construct(public Department $department, public ?string $previousDepartmentName = null) {}

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
            'kind' => 'department_assignment_changed',
            'title' => 'Department assignment updated',
            'message' => $this->previousDepartmentName
                ? "You were moved from {$this->previousDepartmentName} to {$this->department->name}."
                : "You were assigned to the {$this->department->name} department.",
            'url' => route('my-profile'),
            'department_id' => $this->department->id,
        ];
    }
}
