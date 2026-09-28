<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'early_out',
        'lunch_out',
        'lunch_in',
        'lunch_was_scheduled',
        'overtime_in',
        'overtime_out',
        'wfh_request_id',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'early_out' => 'boolean',
        'lunch_was_scheduled' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function wfhRequest(): BelongsTo
    {
        return $this->belongsTo(WfhRequest::class);
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function regularWorkedMinutes(): ?int
    {
        if (! $this->date || ! $this->time_in || ! $this->time_out) {
            return null;
        }

        $start = Carbon::parse($this->date->format('Y-m-d').' '.$this->time_in);
        $end = Carbon::parse($this->date->format('Y-m-d').' '.$this->time_out);

        $lunchStart = Carbon::parse($this->date->format('Y-m-d').' 12:00:00');
        $lunchEnd = Carbon::parse($this->date->format('Y-m-d').' 13:00:00');
        $lunchMinutes = 0;

        if ($start->lessThan($lunchEnd) && $end->greaterThan($lunchStart)) {
            $overlapStart = $start->greaterThan($lunchStart) ? $start : $lunchStart;
            $overlapEnd = $end->lessThan($lunchEnd) ? $end : $lunchEnd;
            $lunchMinutes = $overlapStart->diffInMinutes($overlapEnd);
        }

        return max(0, $start->diffInMinutes($end) - $lunchMinutes);
    }

    public function overtimeMinutes(): ?int
    {
        if (! $this->date || ! $this->overtime_in || ! $this->overtime_out) {
            return null;
        }

        $start = Carbon::parse($this->date->format('Y-m-d').' '.$this->overtime_in);
        $end = Carbon::parse($this->date->format('Y-m-d').' '.$this->overtime_out);

        return $start->diffInMinutes($end);
    }

    public function workedMinutes(): ?int
    {
        $regularMinutes = $this->regularWorkedMinutes();

        if ($regularMinutes === null) {
            return null;
        }

        return $regularMinutes + ($this->overtimeMinutes() ?? 0);
    }

    public function workedDuration(): ?string
    {
        $workedMinutes = $this->workedMinutes();

        if ($workedMinutes === null) {
            return null;
        }

        return sprintf('%dh %02dm', intdiv($workedMinutes, 60), $workedMinutes % 60);
    }

    public function hasMissingClockOut(): bool
    {
        return $this->time_in !== null
            && $this->time_out === null
            && $this->date?->isBefore(today());
    }

    public function formattedTimeIn(): ?string
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format('g:i A') : null;
    }

    public function formattedTimeOut(): ?string
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format('g:i A') : null;
    }

    public function formattedLunchOut(): ?string
    {
        return $this->formatTime($this->lunch_out);
    }

    public function formattedLunchIn(): ?string
    {
        return $this->formatTime($this->lunch_in);
    }

    public function formattedOvertimeIn(): ?string
    {
        return $this->formatTime($this->overtime_in);
    }

    public function formattedOvertimeOut(): ?string
    {
        return $this->formatTime($this->overtime_out);
    }

    public function isOvertimeEligible(): bool
    {
        return $this->time_in !== null && $this->time_in <= '08:00:00' && ! $this->early_out;
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? Carbon::parse($time)->format('g:i A') : null;
    }
}
