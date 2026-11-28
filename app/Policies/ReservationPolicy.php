<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Customers see and cancel their own reservations; the shop sees all of them.
 * Reservations are made on the shop site and changed through ReservationService.
 */
class ReservationPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageRequests;

    public function view(User $user, Model $model): bool
    {
        return $user->can(Permission::ManageRequests->value)
            || ($model instanceof Reservation && $model->customer_id === $user->id);
    }

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

    /** The customer paying (again) for their own reservation. */
    public function pay(User $user, Reservation $reservation): bool
    {
        return $reservation->customer_id === $user->id && $reservation->status === ReservationStatus::AwaitingPayment;
    }

    /** The customer, before collecting; or the shop. */
    public function cancel(User $user, Reservation $reservation): bool
    {
        return $reservation->status->holdsItem()
            && ($reservation->customer_id === $user->id || $user->can(Permission::ManageRequests->value));
    }
}
