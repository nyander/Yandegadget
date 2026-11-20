<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageUsers;

    /**
     * Accounts are kept: they're linked to sales, shipments and payments.
     */
    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
