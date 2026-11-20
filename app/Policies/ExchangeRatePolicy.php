<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ExchangeRatePolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageFinances;

    /**
     * Rates are append-only so past prices stay explainable.
     */
    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
