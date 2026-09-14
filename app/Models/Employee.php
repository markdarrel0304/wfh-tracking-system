<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_number', 'first_name', 'last_name',
        'department_id', 'position', 'work_schedule_id', 'date_hired', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function wfhRequests()
    {
        return $this->hasMany(WfhRequest::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}