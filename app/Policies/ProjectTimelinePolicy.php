<?php

namespace App\Policies;

use App\Models\ProjectTimeline;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectTimelinePolicy
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
    public function view(User $user, ProjectTimeline $projectTimeline): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Semua user yang login bisa membuat timeline
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectTimeline $projectTimeline): bool
    {
        // Project Manager dan Direktur bisa update timeline
        return $user->hasAnyRole(['project_manager', 'direktur']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectTimeline $projectTimeline): bool
    {
        // Hanya Direktur yang bisa delete timeline
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectTimeline $projectTimeline): bool
    {
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectTimeline $projectTimeline): bool
    {
        return $user->hasRole('direktur');
    }
}
