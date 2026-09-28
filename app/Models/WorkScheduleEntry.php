<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkScheduleEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'day',
        'date',
        'shift',
        'time_in',
        'time_out',
        'status',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
