<?php

use App\Enums\Permission;
use App\Models\User;
use App\Support\LiveUpdates;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Broadcast;

/*
 * Private channels for live updates (App\Support\LiveUpdates). Messages only
 * say which area changed; pages then fetch data with their usual permissions.
 */

// A customer's or supplier's own requests, payments and offers.
Broadcast::channel('App.Models.User.{id}', function (User $user, int|string $id) {
    return (string) $user->id === (string) $id;
});

// Everything the back office shows.
Broadcast::channel(LiveUpdates::BACKOFFICE, function (User $user) {
    return $user->canAccessPanel(Filament::getPanel('admin'));
});

// What store staff see: shipments to check in, stock to sell, sales.
Broadcast::channel(LiveUpdates::STORE, function (User $user) {
    return $user->can(Permission::ReceiveShipments->value) || $user->can(Permission::RecordSales->value);
});
