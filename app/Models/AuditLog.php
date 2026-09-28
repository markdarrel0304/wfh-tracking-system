<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /** @var array<string, string> */
    public const EVENT_LABELS = [
        'wfh_request.submitted' => 'WFH request submitted',
        'wfh_request.approved' => 'WFH request approved',
        'wfh_request.rejected' => 'WFH request rejected',
        'attendance_correction.submitted' => 'Attendance correction submitted',
        'attendance_correction.approved' => 'Attendance correction approved',
        'attendance_correction.rejected' => 'Attendance correction rejected',
        'attendance.time_in' => 'Attendance time in recorded',
        'attendance.lunch_started' => 'Attendance lunch break started',
        'attendance.lunch_returned' => 'Attendance lunch break ended',
        'attendance.time_out' => 'Attendance time out recorded',
        'attendance.early_time_out' => 'Attendance early time out recorded',
        'attendance.overtime_started' => 'Attendance overtime started',
        'attendance.overtime_ended' => 'Attendance overtime ended',
        'accomplishment_report.submitted' => 'Accomplishment report submitted',
        'accomplishment_report.reviewed' => 'Accomplishment report reviewed',
        'accomplishment_report.revision_requested' => 'Accomplishment report revision requested',
        'output_attachment.uploaded' => 'Output attachment uploaded',
        'output_attachment.deleted' => 'Output attachment deleted',
        'user.created' => 'User account created',
        'user.updated' => 'User account updated',
        'user.password_reset' => 'User password reset',
        'work_schedule.created' => 'Work schedule created',
        'work_schedule.updated' => 'Work schedule updated',
        'work_schedule.assigned' => 'Work schedule assigned',
        'work_schedule.reverted' => 'Work schedule reverted to default',
    ];

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'summary',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
