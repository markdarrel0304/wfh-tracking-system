<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_number', 'first_name', 'middle_name', 'last_name',
        'department_id', 'position', 'work_schedule_id', 'date_hired', 'status',
        'phone', 'photo', 'address', 'gender', 'date_of_birth', 'nationality', 'marital_status',
        'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone',
    ];

    protected $casts = [
        'date_hired' => 'date',
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function wfhRequests(): HasMany
    {
        return $this->hasMany(WfhRequest::class);
    }

    public function approvedWfhRequestFor(CarbonInterface|string|null $date = null): ?WfhRequest
    {
        return $this->wfhRequests()
            ->approvedOn($date)
            ->oldest('date_from')
            ->first();
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class);
    }

    public function accomplishmentReports(): HasMany
    {
        return $this->hasMany(AccomplishmentReport::class);
    }

    public function workScheduleEntries()
    {
        return $this->hasMany(WorkScheduleEntry::class);
    }

    public function workScheduleAssignments(): HasMany
    {
        return $this->hasMany(WorkScheduleAssignment::class);
    }

    public function effectiveWorkScheduleFor(CarbonInterface|string|null $date = null): ?WorkSchedule
    {
        $effectiveDate = $date instanceof CarbonInterface ? $date->toDateString() : ($date ?? today()->toDateString());

        $this->loadMissing('workSchedule', 'workScheduleAssignments.workSchedule');
        $assignment = $this->workScheduleAssignments
            ->filter(fn (WorkScheduleAssignment $assignment): bool => $assignment->effective_date->toDateString() <= $effectiveDate)
            ->sortByDesc('effective_date')
            ->first();

        return $assignment?->workSchedule ?? $this->workSchedule;
    }
}
