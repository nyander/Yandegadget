<?php

namespace App\Policies;

use App\Enums\Permission;

class CarrierPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageShipments;
}
