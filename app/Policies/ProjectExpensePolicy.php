<?php

namespace App\Policies;

use App\Models\ProjectExpense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectExpensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Semua user bisa melihat expenses
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectExpense $projectExpense): bool
    {
        return true; // Semua user bisa melihat detail expense
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Semua user bisa membuat expense
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectExpense $projectExpense): bool
    {
        // User bisa update expense yang mereka buat, atau direktur/project manager bisa update semua
        return $projectExpense->created_by === $user->id || 
               $user->hasAnyRole(['direktur', 'project_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectExpense $projectExpense): bool
    {
        // User bisa delete expense yang mereka buat (jika masih pending), atau direktur bisa delete semua
        return ($projectExpense->created_by === $user->id && $projectExpense->status === 'pending') || 
               $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can approve expenses.
     */
    public function approve(User $user, ProjectExpense $projectExpense): bool
    {
        // Finance manager, project manager, dan direktur bisa approve
        return $user->hasAnyRole(['direktur', 'project_manager', 'finance_manager']);
    }
}
