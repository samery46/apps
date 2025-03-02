<?php

namespace App\Policies;

use App\Models\Network;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NetworkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_network');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Network $network): bool
    {
        return $user->can('view_network');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_network');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Network $network): bool
    {
        return $user->can('update_network');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Network $network): bool
    {
        return $user->can('delete_network');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Network $network): bool
    {
        return $user->can('delete_any_network');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Network $network): bool
    {
        return $user->can('force_delete_network');
    }
}
