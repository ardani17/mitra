<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Semua user bisa melihat proyek
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return true; // Semua user bisa melihat detail proyek
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya direktur dan project manager yang bisa membuat proyek
        return $user->hasAnyRole(['direktur', 'project_manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Hanya direktur dan project manager yang bisa update proyek
        return $user->hasAnyRole(['direktur', 'project_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Hanya direktur yang bisa menghapus proyek
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can update project status.
     */
    public function updateStatus(User $user, Project $project): bool
    {
        // Direktur dan project manager bisa update status
        return $user->hasAnyRole(['direktur', 'project_manager']);
    }
}
