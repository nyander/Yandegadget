<?php

namespace App\Services;

use App\Enums\OfferStatus;
use App\Enums\Permission;
use App\Enums\StockStatus;
use App\Exceptions\OfferRuleViolation;
use App\Models\Product;
use App\Models\StockUnit;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Notifications\NewSupplierOffer;
use App\Notifications\SupplierOfferReviewed;
use Brick\Money\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Suppliers offer items; the shop buys (accepts) or declines them. Buying
 * creates a stock unit in the UK, ready to go on a shipment.
 */
class SupplierOfferService
{
    /**
     * @param  array{category_id: int, condition_id: int, title: string, brand?: string|null, model?: string|null, serial_number?: string|null, description?: string|null, asking_price: Money}  $data
     * @param  list<UploadedFile>  $photos
     */
    public function submit(Supplier $supplier, User $by, array $data, array $photos = []): SupplierOffer
    {
        if ($supplier->user_id !== $by->id) {
            throw new OfferRuleViolation('You can only submit offers for your own business.');
        }

        $offer = new SupplierOffer;
        $offer->forceFill([
            'supplier_id' => $supplier->id,
            'submitted_by' => $by->id,
            'category_id' => $data['category_id'],
            'condition_id' => $data['condition_id'],
            'title' => $data['title'],
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'description' => $data['description'] ?? null,
            'asking_price_amount' => $data['asking_price']->getMinorAmount()->toInt(),
            'asking_price_currency' => $data['asking_price']->getCurrency()->getCurrencyCode(),
        ])->save();

        foreach ($photos as $photo) {
            $offer->addMedia($photo)->toMediaCollection('photos');
        }

        Notification::send(User::permission(Permission::ReviewSupplierOffers->value)->get(), new NewSupplierOffer($offer));

        return $offer;
    }

    public function withdraw(SupplierOffer $offer, User $by): void
    {
        if ($offer->supplier->user_id !== $by->id) {
            throw new OfferRuleViolation('You can only withdraw your own offers.');
        }

        $this->ensureSubmitted($offer);

        $offer->status = OfferStatus::Withdrawn;
        $offer->save();
    }

    /**
     * Buys the item: creates (or reuses) the catalogue product and a stock
     * unit bought in the UK at the agreed price, with the offer's photos.
     *
     * @param  array<string, mixed>  $terms  agreed_price and list_price (Money), location_id, and optionally product_id.
     */
    public function accept(SupplierOffer $offer, User $by, array $terms): StockUnit
    {
        $this->ensureSubmitted($offer);

        if (! ($terms['agreed_price'] ?? null) instanceof Money || ! ($terms['list_price'] ?? null) instanceof Money || blank($terms['location_id'] ?? null)) {
            throw new OfferRuleViolation('An agreed price, list price and buying location are needed.');
        }

        $unit = DB::transaction(function () use ($offer, $by, $terms) {
            $offer = SupplierOffer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
            $this->ensureSubmitted($offer);

            $product = ! empty($terms['product_id'])
                ? Product::whereKey($terms['product_id'])->firstOrFail()
                : Product::create([
                    'category_id' => $offer->category_id,
                    'name' => $offer->title,
                    'brand' => $offer->brand,
                    'model' => $offer->model,
                ]);

            $unit = StockUnit::create([
                'product_id' => $product->id,
                'condition_id' => $offer->condition_id,
                'supplier_id' => $offer->supplier_id,
                'location_id' => $terms['location_id'],
                'status' => StockStatus::Purchased,
                'cost' => $terms['agreed_price'],
                'price' => $terms['list_price'],
                'condition_notes' => $offer->description,
                'serial_number' => $offer->serial_number,
                'purchased_at' => today(),
                'created_by' => $by->id,
            ]);

            $offer->forceFill([
                'status' => OfferStatus::Accepted,
                'agreed_price_amount' => $terms['agreed_price']->getMinorAmount()->toInt(),
                'stock_unit_id' => $unit->id,
                'reviewed_by' => $by->id,
                'reviewed_at' => now(),
            ])->save();

            return $unit;
        });

        foreach ($offer->getMedia('photos') as $media) {
            $media->copy($unit, 'photos');
        }

        $offer->supplier->user?->notify(new SupplierOfferReviewed($offer->refresh()));

        return $unit;
    }

    public function decline(SupplierOffer $offer, User $by, string $reason): void
    {
        $this->ensureSubmitted($offer);

        if (blank($reason)) {
            throw new OfferRuleViolation('Give the supplier a reason.');
        }

        $offer->forceFill([
            'status' => OfferStatus::Declined,
            'decline_reason' => $reason,
            'reviewed_by' => $by->id,
            'reviewed_at' => now(),
        ])->save();

        $offer->supplier->user?->notify(new SupplierOfferReviewed($offer));
    }

    private function ensureSubmitted(SupplierOffer $offer): void
    {
        if ($offer->status !== OfferStatus::Submitted) {
            throw new OfferRuleViolation("{$offer->reference} has already been {$offer->status->getLabel()}.");
        }
    }
}
