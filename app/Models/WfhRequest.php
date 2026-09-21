<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
