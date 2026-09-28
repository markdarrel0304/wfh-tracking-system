<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WfhRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'request_type',
        'date_from',
        'date_to',
        'start_time',
        'end_time',
        'reason',
        'supporting_document',
        'reviewer_document',
        'status',
        'approver_id',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopeApprovedOn(Builder $query, CarbonInterface|string|null $date = null): Builder
    {
        $workday = $date ? Carbon::parse($date) : today();

        return $query
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $workday)
            ->whereDate('date_to', '>=', $workday);
    }
}
