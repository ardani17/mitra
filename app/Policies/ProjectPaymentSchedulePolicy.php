<?php

namespace App\Policies;

use App\Models\ProjectPaymentSchedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPaymentSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Direktur, finance manager, dan project manager bisa melihat jadwal pembayaran
        return $user->hasAnyRole(['direktur', 'finance_manager', 'project_manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Direktur, finance manager, dan project manager bisa melihat detail jadwal
        return $user->hasAnyRole(['direktur', 'finance_manager', 'project_manager']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Direktur dan finance manager bisa membuat jadwal pembayaran
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Hanya bisa update jika status masih pending
        if ($projectPaymentSchedule->status !== 'pending') {
            return false;
        }

        // Direktur dan finance manager bisa update jadwal
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Hanya bisa delete jika status masih pending
        if ($projectPaymentSchedule->status !== 'pending') {
            return false;
        }

        // Hanya direktur yang bisa menghapus jadwal
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Hanya direktur yang bisa restore
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Hanya direktur yang bisa force delete
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can create billing from schedule.
     */
    public function createBilling(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Hanya bisa create billing jika status pending
        if ($projectPaymentSchedule->status !== 'pending') {
            return false;
        }

        // Direktur dan finance manager bisa create billing dari schedule
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can adjust schedule.
     */
    public function adjust(User $user, ProjectPaymentSchedule $projectPaymentSchedule): bool
    {
        // Hanya bisa adjust jika status pending
        if ($projectPaymentSchedule->status !== 'pending') {
            return false;
        }

        // Direktur dan finance manager bisa adjust schedule
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }

    /**
     * Determine whether the user can export schedules.
     */
    public function export(User $user): bool
    {
        // Direktur, finance manager, dan project manager bisa export
        return $user->hasAnyRole(['direktur', 'finance_manager', 'project_manager']);
    }

    /**
     * Determine whether the user can bulk create schedules.
     */
    public function bulkCreate(User $user): bool
    {
        // Hanya direktur dan finance manager yang bisa bulk create
        return $user->hasAnyRole(['direktur', 'finance_manager']);
    }
}