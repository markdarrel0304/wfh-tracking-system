<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyTask extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'title',
        'task_description',
        'priority',
        'due_time',
        'status',
        'completed_at',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'completed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function outputAttachments(): HasMany
    {
        return $this->hasMany(OutputAttachment::class);
    }

    public function accomplishmentReport(): HasOne
    {
        return $this->hasOne(AccomplishmentReport::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }
}
