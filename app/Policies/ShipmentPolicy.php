<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ShipmentPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageShipments;

    /**
     * Only drafts can be deleted; dispatched shipments are part of the stock history.
     */
    public function delete(User $user, Model $model): bool
    {
        return parent::delete($user, $model) && $model instanceof Shipment && $model->isDraft();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Staff checking items in at the destination.
     */
    public function receive(User $user, Shipment $shipment): bool
    {
        return $user->can(Permission::ReceiveShipments->value)
            && ($user->location_id === null || $user->location_id === $shipment->destination_location_id);
    }
}
