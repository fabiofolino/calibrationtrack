<?php

namespace App\Policies;

use App\Models\CalibrationRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CalibrationRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CalibrationRecord $calibrationRecord): bool
    {
        return $user->company_id === $calibrationRecord->gage->department->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CalibrationRecord $calibrationRecord): bool
    {
        return $user->company_id === $calibrationRecord->gage->department->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CalibrationRecord $calibrationRecord): bool
    {
        return $user->company_id === $calibrationRecord->gage->department->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CalibrationRecord $calibrationRecord): bool
    {
        return $user->company_id === $calibrationRecord->gage->department->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CalibrationRecord $calibrationRecord): bool
    {
        return $user->company_id === $calibrationRecord->gage->department->company_id;
    }
}