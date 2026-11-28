<?php

namespace App\Services;

use App\Enums\Permission;
use App\Enums\ReservationPayment;
use App\Enums\ReservationStatus;
use App\Enums\StockStatus;
use App\Exceptions\ReservationRuleViolation;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\NewReservation;
use App\Notifications\ReservationUpdated;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentService;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Online reservations of items in the store:
 *
 * 1. The customer picks an item and pays in full (the listed price) or a
 *    deposit (a share of it, Shop settings). The item is held while they pay.
 * 2. Once the payment is confirmed it's reserved for them, and they have a few
 *    days (Shop settings) to collect it.
 * 3. Staff sell it to them in store; the amount paid online comes off the price.
 * 4. If they don't collect it in time, or they cancel, or the shop cancels,
 *    they get everything back and the item goes back on sale.
 */
class ReservationService
{
    /** How long the item is held while the customer pays. */
    public const HOLD_MINUTES = 30;

    public function __construct(
        private readonly Pricing $pricing,
        private readonly ShopSettings $settings,
        private readonly PaymentService $payments,
    ) {}

    /**
     * The listed price in the store's currency and the deposit, for showing the choice.
     *
     * @return array{price: Money, deposit: Money, percent: int, days: int}
     */
    public function quote(StockUnit $unit): array
    {
        $price = $this->pricing->convert($unit->price, (string) config('shop.display_currency'));

        if ($price === null) {
            throw new ReservationRuleViolation('Prices aren\'t available right now. Please try again later or visit the store.');
        }

        $percent = $this->settings->reservationDepositPercent();
        // Whole cedis, rounded up: easier to read and to pay by mobile money.
        $deposit = Money::of(
            $price->getAmount()->multipliedBy($percent)->dividedBy(100, 0, RoundingMode::Up),
            $price->getCurrency(),
        );

        if ($deposit->isGreaterThan($price)) {
            $deposit = $price;
        }

        return ['price' => $price, 'deposit' => $deposit, 'percent' => $percent, 'days' => $this->settings->reservationDays()];
    }

    /**
     * Picks an item for the customer. For bulk stock, any available unit of the batch will do.
     */
    public function availableUnit(Product $product, string $reference): ?StockUnit
    {
        $unit = $product->stockUnits()->where('reference', $reference)->first();

        if ($unit === null) {
            return null;
        }

        if ($unit->status === StockStatus::Received) {
            return $unit;
        }

        if ($unit->batch_id !== null) {
            return $product->stockUnits()
                ->where('batch_id', $unit->batch_id)
                ->where('status', StockStatus::Received)
                ->orderBy('id')
                ->first();
        }

        return null;
    }

    /**
     * Holds the item and starts the payment. Returns the checkout URL.
     *
     * @throws ReservationRuleViolation|GatewayException
     */
    public function start(User $customer, StockUnit $unit, ReservationPayment $option, string $callbackUrl): string
    {
        $quote = $this->quote($unit);
        $due = $option === ReservationPayment::Full ? $quote['price'] : $quote['deposit'];

        $reservation = DB::transaction(function () use ($customer, $unit, $option, $quote, $due) {
            $unit = StockUnit::whereKey($unit->id)->lockForUpdate()->firstOrFail();

            if ($unit->status !== StockStatus::Received) {
                throw new ReservationRuleViolation('Sorry, someone has just reserved this item. Please choose another.');
            }

            $unit->transitionTo(StockStatus::Reserved);
            $unit->save();

            $reservation = new Reservation;
            $reservation->forceFill([
                'customer_id' => $customer->id,
                'stock_unit_id' => $unit->id,
                'status' => ReservationStatus::AwaitingPayment,
                'payment_option' => $option,
                'price' => $quote['price'],
                'due' => $due,
                'hold_expires_at' => now()->addMinutes(self::HOLD_MINUTES),
            ])->save();

            return $reservation;
        });

        try {
            return $this->payments->start($reservation, $customer, $due, $callbackUrl);
        } catch (GatewayException $e) {
            $this->cancel($reservation, 'unpaid', 'The payment couldn\'t be started.', notify: false);

            throw $e;
        }
    }

    /**
     * Pays again for a reservation still waiting for payment (e.g. the first attempt was declined).
     *
     * @throws ReservationRuleViolation|GatewayException
     */
    public function retryPayment(Reservation $reservation, string $callbackUrl): string
    {
        if ($reservation->status !== ReservationStatus::AwaitingPayment) {
            throw new ReservationRuleViolation('This reservation doesn\'t need paying.');
        }

        $reservation->forceFill(['hold_expires_at' => now()->addMinutes(self::HOLD_MINUTES)])->save();

        return $this->payments->start($reservation, $reservation->customer, $reservation->due, $callbackUrl);
    }

    /**
     * The payment went through: keep the item for the customer and tell everyone.
     */
    public function paid(Reservation $reservation): void
    {
        $refundInstead = false;

        $updated = DB::transaction(function () use ($reservation, &$refundInstead) {
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $unit = StockUnit::whereKey($reservation->stock_unit_id)->lockForUpdate()->firstOrFail();

            if ($reservation->status === ReservationStatus::Cancelled) {
                // Paid after the hold ran out: keep it if the item is still free, otherwise refund.
                if ($unit->status !== StockStatus::Received) {
                    $refundInstead = true;

                    return $reservation;
                }

                $unit->transitionTo(StockStatus::Reserved);
                $unit->save();
            } elseif ($reservation->status !== ReservationStatus::AwaitingPayment) {
                return null;
            }

            $reservation->forceFill([
                'status' => ReservationStatus::Reserved,
                'paid_at' => now(),
                'collect_by' => now()->addDays($this->settings->reservationDays())->endOfDay(),
                'hold_expires_at' => null,
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancel_reason' => null,
            ])->save();

            return $reservation;
        });

        if ($refundInstead) {
            $this->refund($updated, 'The item was reserved by someone else before your payment arrived.');

            return;
        }

        if ($updated === null) {
            return;
        }

        $updated->customer->notify(new ReservationUpdated($updated));
        Notification::send($this->shopPeople($updated), new NewReservation($updated));
    }

    /**
     * The active reservation for an item at the till, if any.
     */
    public function activeFor(StockUnit $unit): ?Reservation
    {
        return Reservation::where('stock_unit_id', $unit->id)
            ->whereIn('status', [ReservationStatus::AwaitingPayment, ReservationStatus::Reserved])
            ->with('customer')
            ->latest('id')
            ->first();
    }

    /** Sold to the customer in store. */
    public function collected(Reservation $reservation, Sale $sale): void
    {
        $reservation->forceFill([
            'status' => ReservationStatus::Collected,
            'collected_at' => $sale->sold_at,
        ])->save();
    }

    /** The sale was voided: the item is reserved for the customer again. */
    public function saleVoided(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::Collected) {
            return;
        }

        $unit = $reservation->stockUnit;

        if ($unit->status === StockStatus::Received) {
            $unit->transitionTo(StockStatus::Reserved);
            $unit->save();
        }

        $reservation->forceFill(['status' => ReservationStatus::Reserved, 'collected_at' => null])->save();
    }

    /**
     * Cancels a reservation: the item goes back on sale and anything paid is refunded in full.
     *
     * @param  'customer'|'shop'|'not_collected'|'unpaid'  $by
     *
     * @throws ReservationRuleViolation|GatewayException
     */
    public function cancel(Reservation $reservation, string $by, string $reason, bool $notify = true): void
    {
        if (! $reservation->status->holdsItem()) {
            throw new ReservationRuleViolation("{$reservation->reference} can't be cancelled now.");
        }

        $wasPaid = $reservation->status === ReservationStatus::Reserved;

        DB::transaction(function () use ($reservation, $by, $reason) {
            $unit = StockUnit::whereKey($reservation->stock_unit_id)->lockForUpdate()->firstOrFail();

            if ($unit->status === StockStatus::Reserved) {
                $unit->transitionTo(StockStatus::Received);
                $unit->save();
            }

            $reservation->forceFill([
                'status' => ReservationStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_by' => $by,
                'cancel_reason' => $reason,
            ])->save();
        });

        if ($wasPaid) {
            $this->refund($reservation, null, notify: false);
        }

        if ($notify) {
            $reservation->customer->notify(new ReservationUpdated($reservation->fresh() ?? $reservation));
        }
    }

    /**
     * Releases items not paid for within the hold, and refunds items not collected
     * in time. Runs on a schedule. Returns how many were released.
     */
    public function releaseExpired(): int
    {
        $released = 0;

        Reservation::where('status', ReservationStatus::AwaitingPayment)
            ->where('hold_expires_at', '<', now())
            ->each(function (Reservation $reservation) use (&$released) {
                $this->cancel($reservation, 'unpaid', 'Payment wasn\'t completed in time, so the item went back on sale.', notify: false);
                $released++;
            });

        Reservation::where('status', ReservationStatus::Reserved)
            ->where('collect_by', '<', now())
            ->each(function (Reservation $reservation) use (&$released) {
                try {
                    $this->cancel($reservation, 'not_collected', 'Not collected in time, so it went back on sale. Everything you paid has been refunded.');
                    $released++;
                } catch (ReservationRuleViolation|GatewayException $e) {
                    report($e);
                }
            });

        return $released;
    }

    /**
     * @throws GatewayException
     */
    private function refund(Reservation $reservation, ?string $reason, bool $notify = true): void
    {
        $payment = $reservation->paidPayment();

        if ($payment !== null && $payment->refunded_at === null) {
            $this->payments->refund($payment);
        }

        if ($reason !== null) {
            $reservation->forceFill(['cancel_reason' => $reason])->save();
        }

        if ($notify) {
            $reservation->customer->notify(new ReservationUpdated($reservation));
        }

        Log::info('Reservation refunded', ['reference' => $reservation->reference]);
    }

    /**
     * Staff who sell at the item's store, and the admins.
     *
     * @return Collection<int, User>
     */
    private function shopPeople(Reservation $reservation): Collection
    {
        $locationId = $reservation->stockUnit->location_id;

        return User::permission([Permission::RecordSales->value, Permission::ManageRequests->value])
            ->get()
            ->filter(fn (User $user) => $user->can(Permission::ManageRequests->value)
                || $user->location_id === null
                || $user->location_id === $locationId)
            ->values()
            ->toBase();
    }
}
