<?php

namespace App\Policies;

use App\Models\Software;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SoftwarePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_software');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Software $software): bool
    {
        return $user->can('view_software');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_software');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Software $software): bool
    {
        return $user->can('update_software');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Software $software): bool
    {
        return $user->can('delete_software');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Software $software): bool
    {
        return $user->can('delete_any_software');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Software $software): bool
    {
        return $user->can('force_delete_software');
    }
}
