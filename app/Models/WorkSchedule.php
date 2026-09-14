<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = ['name', 'days_json', 'time_in', 'time_out'];
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}