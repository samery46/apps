<?php

namespace App\Policies;

use App\Models\RequestItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RequestItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_requestitem');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RequestItem $requestItem): bool
    {
        return $user->can('view_requestitem');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_requestitem');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RequestItem $requestItem): bool
    {
        return $user->can('update_requestitem');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RequestItem $requestItem): bool
    {
        return $user->can('delete_requestitem');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RequestItem $requestItem): bool
    {
        return $user->can('delete_any_requestitem');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RequestItem $requestItem): bool
    {
        return $user->can('force_delete_requestitem');
    }
}
