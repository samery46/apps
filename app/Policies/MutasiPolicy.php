<?php

namespace App\Policies;

use App\Models\Mutasi;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MutasiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_mutasi');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Mutasi $mutasi): bool
    {
        return $user->can('view_mutasi');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_mutasi');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Mutasi $mutasi): bool
    {
        return $user->can('update_mutasi');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mutasi $mutasi): bool
    {
        return $user->can('delete_mutasi');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Mutasi $mutasi): bool
    {
        return $user->can('delete_any_mutasi');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Mutasi $mutasi): bool
    {
        return $user->can('force_delete_mutasi');
    }
}
