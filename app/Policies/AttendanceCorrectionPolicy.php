<?php

namespace App\Policies;

use App\Models\AttendanceCorrection;
use App\Models\User;

class AttendanceCorrectionPolicy
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
    public function view(User $user, AttendanceCorrection $attendanceCorrection): bool
    {
        return $user->employee?->id === $attendanceCorrection->employee_id
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
    public function update(User $user, AttendanceCorrection $attendanceCorrection): bool
    {
        return false;
    }

    public function approve(User $user, AttendanceCorrection $attendanceCorrection): bool
    {
        return in_array($user->role, ['admin', 'supervisor'], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttendanceCorrection $attendanceCorrection): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AttendanceCorrection $attendanceCorrection): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AttendanceCorrection $attendanceCorrection): bool
    {
        return false;
    }
}
