<?php

namespace App\Http\Presenters;

use App\Enums\ReservationPayment;
use App\Models\Reservation;
use App\Services\Pricing;

/**
 * Shapes reservations for the customer's pages. Cedi amounts only.
 */
class ReservationPresenter
{
    public function __construct(private readonly Pricing $pricing) {}

    /**
     * @return array<string, mixed>
     */
    public function show(Reservation $reservation): array
    {
        $reservation->loadMissing(['stockUnit.product.media', 'stockUnit.condition', 'stockUnit.media']);
        $unit = $reservation->stockUnit;
        $photo = $unit->getFirstMedia('photos') ?? $unit->product->getFirstMedia('gallery');
        $payment = $reservation->paidPayment();

        return [
            'id' => $reservation->id,
            'reference' => $reservation->reference,
            'status' => $reservation->status->value,
            'status_label' => $reservation->status->getLabel(),
            'payment_option' => $reservation->payment_option->value,
            'paid_in_full' => $reservation->payment_option === ReservationPayment::Full,
            'item' => [
                'reference' => $unit->reference,
                'name' => $unit->product->name,
                'slug' => $unit->product->slug,
                'grade' => $unit->condition->grade,
                'condition' => $unit->condition->name,
                'image' => $photo?->getUrl('thumb'),
            ],
            'price' => $this->pricing->present($reservation->price),
            'due' => $this->pricing->present($reservation->due),
            'balance' => $this->pricing->present($reservation->balance()),
            'paid_at' => $reservation->paid_at?->toIso8601String(),
            'collect_by' => $reservation->collect_by?->toIso8601String(),
            'hold_expires_at' => $reservation->hold_expires_at?->toIso8601String(),
            'cancelled_at' => $reservation->cancelled_at?->toIso8601String(),
            'cancel_reason' => $reservation->cancel_reason,
            'refunded' => $payment?->refunded_at !== null,
            'channel' => $payment?->channel,
            'created_at' => $reservation->created_at?->toIso8601String(),
        ];
    }
}
