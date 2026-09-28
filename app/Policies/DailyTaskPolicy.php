<?php

namespace App\Policies;

use App\Models\DailyTask;
use App\Models\User;

class DailyTaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->employee !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DailyTask $dailyTask): bool
    {
        return $user->employee?->id === $dailyTask->employee_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->employee !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DailyTask $dailyTask): bool
    {
        return $user->employee?->id === $dailyTask->employee_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DailyTask $dailyTask): bool
    {
        return $user->employee?->id === $dailyTask->employee_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DailyTask $dailyTask): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DailyTask $dailyTask): bool
    {
        return false;
    }
}
