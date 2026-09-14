<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WfhRequest extends Model
{
    protected $fillable = [
    'employee_id', 'date_from', 'date_to', 'reason',
    'status', 'approver_id', 'approved_at', 'remarks',
];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}