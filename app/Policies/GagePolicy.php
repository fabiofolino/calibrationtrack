<?php

namespace App\Policies;

use App\Models\Gage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GagePolicy
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
    public function view(User $user, Gage $gage): bool
    {
        return $user->company_id === $gage->department->company_id;
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
    public function update(User $user, Gage $gage): bool
    {
        return $user->company_id === $gage->department->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Gage $gage): bool
    {
        return $user->company_id === $gage->department->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Gage $gage): bool
    {
        return $user->company_id === $gage->department->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Gage $gage): bool
    {
        return $user->company_id === $gage->department->company_id;
    }
}