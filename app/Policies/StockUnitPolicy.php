<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\StockStatus;
use App\Models\StockUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockUnitPolicy extends CataloguePolicy
{
    /**
     * Sold units are part of the sales record and can't be deleted.
     */
    public function delete(User $user, Model $model): bool
    {
        return parent::delete($user, $model)
            && $model->getAttribute('status') !== StockStatus::Sold;
    }

    /**
     * Staff sell items in store at their own location.
     */
    public function sell(User $user, StockUnit $unit): bool
    {
        return $user->can(Permission::RecordSales->value)
            && in_array($unit->status, [StockStatus::Received, StockStatus::Reserved], true)
            && ($user->location_id === null || $user->location_id === $unit->location_id);
    }
}
