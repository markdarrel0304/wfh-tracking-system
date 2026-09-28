<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor'], true);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->employee?->is($employee)
            || in_array($user->role, ['admin', 'supervisor'], true);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->role === 'admin';
    }
}
