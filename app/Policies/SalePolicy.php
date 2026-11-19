<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Sales are recorded on the staff sale screen and never edited or deleted;
 * mistakes are voided so the record stays.
 */
class SalePolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ViewReports;

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

    public function void(User $user, Sale $sale): bool
    {
        return $user->can(Permission::ManageFinances->value) && ! $sale->isVoided();
    }

    /**
     * Staff can see sales recorded at their own store.
     */
    public function viewReceipt(User $user, Sale $sale): bool
    {
        return $user->can(Permission::ViewReports->value)
            || ($user->can(Permission::RecordSales->value)
                && ($user->location_id === null || $user->location_id === $sale->location_id));
    }
}
