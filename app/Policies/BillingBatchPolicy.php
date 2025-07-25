<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BillingBatch;

class BillingBatchPolicy
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
    public function view(User $user, BillingBatch $billingBatch): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Finance Manager and Direktur can create billing batches
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BillingBatch $billingBatch): bool
    {
        // Only Finance Manager and Direktur can update billing batches
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BillingBatch $billingBatch): bool
    {
        // Only Finance Manager and Direktur can delete billing batches
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BillingBatch $billingBatch): bool
    {
        return $this->delete($user, $billingBatch);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BillingBatch $billingBatch): bool
    {
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can update status.
     */
    public function updateStatus(User $user, BillingBatch $billingBatch): bool
    {
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can upload documents.
     */
    public function uploadDocument(User $user, BillingBatch $billingBatch): bool
    {
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }
}
