<?php

namespace App\Notifications;

use App\Models\AttendanceCorrection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceCorrectionReviewed extends Notification
{
    use Queueable;

    public function __construct(public AttendanceCorrection $attendanceCorrection) {}

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
        $isApproved = $this->attendanceCorrection->status === 'approved';

        return [
            'kind' => 'attendance_correction_reviewed',
            'title' => $isApproved ? 'Attendance correction approved' : 'Attendance correction not approved',
            'message' => sprintf(
                'Your attendance correction for %s was %s.',
                $this->attendanceCorrection->attendance->date->format('M j, Y'),
                $isApproved ? 'approved' : 'not approved',
            ),
            'url' => route('attendance.corrections'),
            'attendance_correction_id' => $this->attendanceCorrection->id,
        ];
    }
}
