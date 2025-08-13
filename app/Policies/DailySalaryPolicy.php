<?php

namespace App\Policies;

use App\Models\DailySalary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailySalaryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DailySalary $dailySalary): bool
    {
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DailySalary $dailySalary): bool
    {
        // Cannot update if already released
        if ($dailySalary->is_released) {
            return false;
        }
        
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DailySalary $dailySalary): bool
    {
        // Cannot delete if already released
        if ($dailySalary->is_released) {
            return false;
        }
        
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DailySalary $dailySalary): bool
    {
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DailySalary $dailySalary): bool
    {
        return $user->hasRole('direktur');
    }
}
