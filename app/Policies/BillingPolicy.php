<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProjectBilling;

class BillingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectBilling $billing): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Finance Manager and above can create billings
        return $user->hasAnyRole(['finance_manager', 'project_manager', 'direktur']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectBilling $billing): bool
    {
        // Only Finance Manager and above can update billings
        return $user->hasAnyRole(['finance_manager', 'project_manager', 'direktur']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectBilling $billing): bool
    {
        // Only Finance Manager and above can delete billings
        return $user->hasAnyRole(['finance_manager', 'project_manager', 'direktur']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectBilling $billing): bool
    {
        return $this->delete($user, $billing);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectBilling $billing): bool
    {
        return $user->hasRole('direktur');
    }
}
