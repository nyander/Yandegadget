<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared rules for catalogue records managed in the admin panel: anyone with
 * the catalogue permission can manage them. Subclasses can tighten individual actions.
 */
abstract class CataloguePolicy
{
    protected Permission $permission = Permission::ManageCatalogue;

    public function viewAny(User $user): bool
    {
        return $user->can($this->permission->value);
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->permission->value);
    }

    public function create(User $user): bool
    {
        return $user->can($this->permission->value);
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->permission->value);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can($this->permission->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can($this->permission->value);
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->can($this->permission->value);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can($this->permission->value);
    }

    public function reorder(User $user): bool
    {
        return $user->can($this->permission->value);
    }

    // No forceDelete: records with history are soft-deleted or protected by foreign keys.
}
