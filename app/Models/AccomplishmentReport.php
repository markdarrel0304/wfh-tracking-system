<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccomplishmentReport extends Model
{
    protected $fillable = [
        'employee_id',
        'daily_task_id',
        'title',
        'category',
        'progress_status',
        'date',
        'summary',
        'blockers',
        'next_steps',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'revision_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'revision_requested_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function dailyTask(): BelongsTo
    {
        return $this->belongsTo(DailyTask::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }

    public function outputAttachments(): HasMany
    {
        return $this->hasMany(OutputAttachment::class);
    }
}
