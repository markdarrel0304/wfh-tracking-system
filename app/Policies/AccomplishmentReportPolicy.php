<?php

namespace App\Policies;

use App\Models\AccomplishmentReport;
use App\Models\User;

class AccomplishmentReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->employee !== null || in_array($user->role, ['admin', 'supervisor'], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccomplishmentReport $accomplishmentReport): bool
    {
        return $user->employee?->id === $accomplishmentReport->employee_id
            || in_array($user->role, ['admin', 'supervisor'], true);
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
    public function update(User $user, AccomplishmentReport $accomplishmentReport): bool
    {
        return $user->employee?->id === $accomplishmentReport->employee_id;
    }

    public function approve(User $user, AccomplishmentReport $accomplishmentReport): bool
    {
        return in_array($user->role, ['admin', 'supervisor'], true)
            && $accomplishmentReport->status === 'submitted'
            && $accomplishmentReport->submitted_at !== null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccomplishmentReport $accomplishmentReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AccomplishmentReport $accomplishmentReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AccomplishmentReport $accomplishmentReport): bool
    {
        return false;
    }
}
