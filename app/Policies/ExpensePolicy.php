<?php

namespace App\Policies;

use App\Models\ProjectExpense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua role dapat melihat daftar pengeluaran
        return $user->hasAnyRole(['direktur', 'project_manager', 'finance_manager', 'staf']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectExpense $expense): bool
    {
        // Semua role dapat melihat detail pengeluaran
        return $user->hasAnyRole(['direktur', 'project_manager', 'finance_manager', 'staf']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Semua role dapat membuat pengeluaran (untuk pengajuan)
        return $user->hasAnyRole(['direktur', 'project_manager', 'finance_manager', 'staf']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectExpense $expense): bool
    {
        // Hanya pembuat expense yang dapat mengupdate jika status masih pending
        // Atau Direktur dan Project Manager dapat mengupdate
        if ($user->hasAnyRole(['direktur', 'project_manager'])) {
            return true;
        }

        // Pembuat expense dapat update jika status masih pending
        return $expense->created_by === $user->id && $expense->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectExpense $expense): bool
    {
        // Hanya Direktur yang dapat menghapus pengeluaran
        // Atau pembuat expense jika status masih pending
        if ($user->hasRole('direktur')) {
            return true;
        }

        return $expense->created_by === $user->id && $expense->status === 'pending';
    }

    /**
     * Determine whether the user can approve the expense.
     */
    public function approve(User $user, ProjectExpense $expense): bool
    {
        // Hanya Finance Manager, Project Manager, dan Direktur yang dapat approve
        return $user->hasAnyRole(['direktur', 'project_manager', 'finance_manager']);
    }

    /**
     * Determine whether the user can reject the expense.
     */
    public function reject(User $user, ProjectExpense $expense): bool
    {
        // Hanya Finance Manager, Project Manager, dan Direktur yang dapat reject
        return $user->hasAnyRole(['direktur', 'project_manager', 'finance_manager']);
    }

    /**
     * Determine whether the user can review the expense.
     */
    public function review(User $user, ProjectExpense $expense): bool
    {
        // Finance Manager dapat review expense
        return $user->hasRole('finance_manager');
    }

    /**
     * Determine whether the user can export expenses.
     */
    public function export(User $user): bool
    {
        // Direktur dan Finance Manager dapat export data pengeluaran
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }
}
