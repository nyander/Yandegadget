<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProductRequestPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageRequests;

    /**
     * Customers see their own requests; the shop sees all of them.
     */
    public function view(User $user, Model $model): bool
    {
        return $user->can(Permission::ManageRequests->value)
            || ($model instanceof ProductRequest && $model->customer_id === $user->id);
    }

    /** Requests are made by customers on the shop site, not in the admin. */
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

    public function pay(User $user, ProductRequest $request): bool
    {
        return $request->customer_id === $user->id;
    }
}
