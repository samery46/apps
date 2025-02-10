<?php

namespace App\Policies;

use App\Models\Numbering;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NumberingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_numbering');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Numbering $numbering): bool
    {
        return $user->can('view_numbering');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_numbering');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Numbering $numbering): bool
    {
        return $user->can('update_numbering');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Numbering $numbering): bool
    {
        return $user->can('delete_numbering');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Numbering $numbering): bool
    {
        return $user->can('delete_any_numbering');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Numbering $numbering): bool
    {
        return $user->can('force_delete_numbering');
    }
}
