<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
    'employee_id', 'date', 'time_in', 'time_out', 'wfh_request_id', 'status',
];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function wfhRequest()
    {
        return $this->belongsTo(WfhRequest::class);
    }
}