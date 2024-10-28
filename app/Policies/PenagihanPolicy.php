<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Penagihan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PenagihanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_penagihan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Penagihan $penagihan): bool
    {
        return $user->can('view_penagihan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_penagihan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Penagihan $penagihan): bool
    {
        return $user->can('update_penagihan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Penagihan $penagihan): bool
    {
        return $user->can('delete_penagihan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_penagihan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Penagihan $penagihan): bool
    {
        return $user->can('force_delete_penagihan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_penagihan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Penagihan $penagihan): bool
    {
        return $user->can('restore_penagihan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_penagihan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Penagihan $penagihan): bool
    {
        return $user->can('replicate_penagihan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_penagihan');
    }
}
