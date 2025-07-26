<?php

namespace App\Policies;

use App\Models\GageToleranceRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GageToleranceRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view tolerance records within their company scope
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GageToleranceRecord $gageToleranceRecord): bool
    {
        return $gageToleranceRecord->gage->department->company_id === $user->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Users can create tolerance records for gages in their company
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GageToleranceRecord $gageToleranceRecord): bool
    {
        return $gageToleranceRecord->gage->department->company_id === $user->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GageToleranceRecord $gageToleranceRecord): bool
    {
        return $gageToleranceRecord->gage->department->company_id === $user->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GageToleranceRecord $gageToleranceRecord): bool
    {
        return $gageToleranceRecord->gage->department->company_id === $user->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GageToleranceRecord $gageToleranceRecord): bool
    {
        return $gageToleranceRecord->gage->department->company_id === $user->company_id;
    }
}
