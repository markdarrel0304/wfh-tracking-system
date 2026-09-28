<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutputAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'accomplishment_report_id',
        'daily_task_id',
        'file_path',
        'file_name',
        'mime_type',
        'size_bytes',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'size_bytes' => 'integer',
        ];
    }

    public function accomplishmentReport(): BelongsTo
    {
        return $this->belongsTo(AccomplishmentReport::class);
    }

    public function dailyTask(): BelongsTo
    {
        return $this->belongsTo(DailyTask::class);
    }
}
