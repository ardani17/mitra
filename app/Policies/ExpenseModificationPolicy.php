<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProjectExpense;
use App\Models\ExpenseModificationApproval;

class ExpenseModificationPolicy
{
    /**
     * Determine whether the user can view any modification requests.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['finance_manager', 'direktur', 'project_manager']);
    }

    /**
     * Determine whether the user can view the modification request.
     */
    public function view(User $user, ExpenseModificationApproval $modification): bool
    {
        // User can view if they are the requester or have approval permissions
        return $modification->requested_by === $user->id || 
               $user->hasAnyRole(['finance_manager', 'direktur', 'project_manager']);
    }

    /**
     * Determine whether the user can request modifications for an expense.
     */
    public function requestModification(User $user, ProjectExpense $expense): bool
    {
        // User can request modification if:
        // 1. They created the expense, OR
        // 2. They have project manager or higher role
        return $expense->user_id === $user->id || 
               $user->hasAnyRole(['project_manager', 'finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can approve modification requests.
     */
    public function approve(User $user, ExpenseModificationApproval $modification): bool
    {
        // Users cannot approve their own requests
        if ($modification->requested_by === $user->id) {
            return false;
        }

        // Check if user has required role for this type of approval
        $requiredLevels = $modification->getRequiredApprovalLevels();
        $userRoles = $user->roles->pluck('name')->toArray();

        return !empty(array_intersect($userRoles, $requiredLevels));
    }

    /**
     * Determine whether the user can cancel modification requests.
     */
    public function cancel(User $user, ExpenseModificationApproval $modification): bool
    {
        // Only the requester can cancel their own pending requests
        return $modification->requested_by === $user->id && $modification->isPending();
    }

    /**
     * Determine whether the user can perform bulk approvals.
     */
    public function bulkApprove(User $user): bool
    {
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can export modification requests.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }

    /**
     * Determine whether the user can delete modification requests.
     */
    public function delete(User $user, ExpenseModificationApproval $modification): bool
    {
        // Only finance managers and directors can delete modification requests
        return $user->hasAnyRole(['finance_manager', 'direktur']);
    }
}