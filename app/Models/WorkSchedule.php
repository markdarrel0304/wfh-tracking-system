<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'days_json',
        'time_in',
        'time_out',
        'lunch_start',
        'lunch_end',
        'overtime_start',
        'overtime_minimum_minutes',
        'overtime_maximum_minutes',
    ];

    protected function casts(): array
    {
        return [
            'days_json' => 'array',
            'overtime_minimum_minutes' => 'integer',
            'overtime_maximum_minutes' => 'integer',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkScheduleAssignment::class);
    }
}
