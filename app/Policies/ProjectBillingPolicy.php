<?php

namespace App\Policies;

use App\Models\ProjectBilling;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectBillingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Hanya direktur dan finance manager yang bisa melihat billing
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectBilling $projectBilling): bool
    {
        // Hanya direktur dan finance manager yang bisa melihat detail billing
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya direktur dan finance manager yang bisa membuat billing
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectBilling $projectBilling): bool
    {
        // Hanya direktur dan finance manager yang bisa update billing
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectBilling $projectBilling): bool
    {
        // Hanya direktur yang bisa menghapus billing
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can send invoice.
     */
    public function sendInvoice(User $user, ProjectBilling $projectBilling): bool
    {
        // Direktur dan finance manager bisa mengirim invoice
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can mark as paid.
     */
    public function markAsPaid(User $user, ProjectBilling $projectBilling): bool
    {
        // Direktur dan finance manager bisa mark as paid
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }
}
