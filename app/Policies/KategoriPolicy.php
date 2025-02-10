<?php

namespace App\Policies;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KategoriPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_kategori');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Kategori $kategori): bool
    {
        return $user->can('view_kategori');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_kategori');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Kategori $kategori): bool
    {
        return $user->can('update_kategori');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Kategori $kategori): bool
    {
        return $user->can('delete_kategori');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Kategori $kategori): bool
    {
        return $user->can('delete_any_kategori');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Kategori $kategori): bool
    {
        return $user->can('force_delete_kategori');
    }
}
