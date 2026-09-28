<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WfhRequest;

class WfhRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor'], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WfhRequest $wfhRequest): bool
    {
        return $user->employee && $user->employee->id === $wfhRequest->employee_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'employee' && $user->employee !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WfhRequest $wfhRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can approve or reject a pending request.
     */
    public function approve(User $user, WfhRequest $wfhRequest): bool
    {
        return in_array($user->role, ['admin', 'supervisor'], true);
    }

    public function viewDocument(User $user, WfhRequest $wfhRequest): bool
    {
        return $user->employee?->id === $wfhRequest->employee_id
            || in_array($user->role, ['admin', 'supervisor'], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WfhRequest $wfhRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WfhRequest $wfhRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WfhRequest $wfhRequest): bool
    {
        return false;
    }
}
