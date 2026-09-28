<?php

namespace App\Policies;

use App\Models\OutputAttachment;
use App\Models\User;

class OutputAttachmentPolicy
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
    public function view(User $user, OutputAttachment $outputAttachment): bool
    {
        return $user->employee?->id === $outputAttachment->accomplishmentReport->employee_id
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
    public function update(User $user, OutputAttachment $outputAttachment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OutputAttachment $outputAttachment): bool
    {
        return $user->employee?->id === $outputAttachment->accomplishmentReport->employee_id
            && $outputAttachment->accomplishmentReport->status !== 'reviewed';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OutputAttachment $outputAttachment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OutputAttachment $outputAttachment): bool
    {
        return false;
    }
}
