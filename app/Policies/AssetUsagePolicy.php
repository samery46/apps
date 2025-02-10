<?php

namespace App\Policies;

use App\Models\AssetUsage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssetUsagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_assetusage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AssetUsage $assetUsage): bool
    {
        return $user->can('view_assetusage');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_assetusage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AssetUsage $assetUsage): bool
    {
        return $user->can('update_assetusage');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AssetUsage $assetUsage): bool
    {
        return $user->can('delete_assetusage');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AssetUsage $assetUsage): bool
    {
        return $user->can('delete_any_assetusage');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AssetUsage $assetUsage): bool
    {
        return $user->can('force_delete_assetusage');
    }
}
