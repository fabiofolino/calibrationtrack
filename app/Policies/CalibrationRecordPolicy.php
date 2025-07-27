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
        // First check if user belongs to the same company
        if ($user->company_id !== $calibrationRecord->gage->department->company_id) {
            return false;
        }

        // Admins can delete any calibration record in their company
        if ($user->isAdmin()) {
            return true;
        }

        // Regular users can only delete their own calibration records
        if ($calibrationRecord->user_id !== $user->id) {
            return false;
        }

        // Prevent deletion of calibration records older than 30 days
        // This helps maintain data integrity for compliance
        $thirtyDaysAgo = now()->subDays(30);
        if ($calibrationRecord->created_at->lt($thirtyDaysAgo)) {
            return false;
        }

        // Don't allow deletion if there are measurement groups with data
        // This prevents accidental loss of detailed measurement data
        if ($calibrationRecord->measurementGroups()->whereHas('measurements', function($query) {
            $query->whereNotNull('as_found_value')->orWhereNotNull('as_left_value');
        })->exists()) {
            return false;
        }

        return true;
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