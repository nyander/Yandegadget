<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\SupplierOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SupplierOfferPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ReviewSupplierOffers;

    /**
     * Suppliers see their own offers; the shop sees all of them.
     */
    public function view(User $user, Model $model): bool
    {
        return $user->can(Permission::ReviewSupplierOffers->value)
            || ($model instanceof SupplierOffer && $model->supplier->user_id === $user->id);
    }

    /** Offers come from the supplier portal, not the admin. */
    public function create(User $user): bool
    {
        return false;
    }

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
