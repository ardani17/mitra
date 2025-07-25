<?php

namespace App\Policies;

use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua user yang sudah login bisa melihat daftar dokumen
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectDocument $projectDocument): bool
    {
        // Semua user yang sudah login bisa melihat dokumen
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Semua user yang sudah login bisa upload dokumen
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectDocument $projectDocument): bool
    {
        // Hanya yang upload atau direktur yang bisa update
        return $user->id === $projectDocument->uploaded_by || 
               $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectDocument $projectDocument): bool
    {
        // Hanya yang upload atau direktur yang bisa hapus
        return $user->id === $projectDocument->uploaded_by || 
               $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectDocument $projectDocument): bool
    {
        return $user->hasRole('direktur');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectDocument $projectDocument): bool
    {
        return $user->hasRole('direktur');
    }
}
