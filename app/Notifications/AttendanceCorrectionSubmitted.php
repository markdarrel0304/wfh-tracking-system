<?php

namespace App\Notifications;

use App\Models\AttendanceCorrection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceCorrectionSubmitted extends Notification
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
        $isMissingClockOut = $this->attendanceCorrection->attendance->hasMissingClockOut();

        return [
            'kind' => 'attendance_correction_submitted',
            'title' => $isMissingClockOut ? 'Missing clock-out correction' : 'New attendance correction',
            'message' => sprintf(
                $isMissingClockOut
                    ? '%s %s submitted a missing clock-out correction for %s.'
                    : '%s %s requested an attendance correction for %s.',
                $this->attendanceCorrection->employee->first_name,
                $this->attendanceCorrection->employee->last_name,
                $this->attendanceCorrection->attendance->date->format('M j, Y'),
            ),
            'url' => route('approvals.attendance-corrections'),
            'attendance_correction_id' => $this->attendanceCorrection->id,
        ];
    }
}
