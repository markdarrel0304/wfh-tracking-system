<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'location', 'department_head', 'department_head_count', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'department_head_count' => 'integer',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
