<?php

namespace App\Policies;

use App\Models\SalaryRelease;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalaryReleasePolicy
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
    public function view(User $user, SalaryRelease $salaryRelease): bool
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
    public function update(User $user, SalaryRelease $salaryRelease): bool
    {
        // Only direktur can release salaries (change status to released)
        if ($salaryRelease->status === 'draft') {
            return $user->hasAnyRole(['direktur', 'finance_manager']);
        }
        
        // Only direktur can modify released salaries
        if ($salaryRelease->status === 'released') {
            return $user->hasRole('direktur');
        }
        
        // Cannot modify paid salaries
        if ($salaryRelease->status === 'paid') {
            return false;
        }
        
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalaryRelease $salaryRelease): bool
    {
        // Cannot delete if already released or paid
        if ($salaryRelease->is_released) {
            return false;
        }
        
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SalaryRelease $salaryRelease): bool
    {
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SalaryRelease $salaryRelease): bool
    {
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can release salary (change status to released)
     */
    public function release(User $user, SalaryRelease $salaryRelease): bool
    {
        return $user->hasRole('direktur') && $salaryRelease->status === 'draft';
    }

    /**
     * Determine whether the user can mark salary as paid
     */
    public function markAsPaid(User $user, SalaryRelease $salaryRelease): bool
    {
        return $user->hasAnyRole(['direktur', 'finance_manager']) && $salaryRelease->status === 'released';
    }
}
