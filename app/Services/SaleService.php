<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\StockStatus;
use App\Exceptions\SaleRuleViolation;
use App\Models\Sale;
use App\Models\StockUnit;
use App\Models\User;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * In-store sales. Prices are negotiated, but never below the floor: a share of
 * the listed price in the store's currency (70% by default, set by the admin).
 */
class SaleService
{
    public function __construct(
        private readonly Pricing $pricing,
        private readonly ShopSettings $settings,
    ) {}

    public function quote(StockUnit $unit): SaleQuote
    {
        $currency = $unit->location->currency;
        $listed = $this->pricing->convert($unit->price, $currency);

        if ($listed === null) {
            throw new SaleRuleViolation("There's no {$unit->price->getCurrency()->getCurrencyCode()} to {$currency} exchange rate yet. Ask the admin to set one.");
        }

        $percent = $this->settings->negotiationFloorPercent();

        return new SaleQuote(
            listPrice: $unit->price,
            listed: $listed,
            floor: $listed->multipliedBy($percent)->dividedBy(100, RoundingMode::Up),
            floorPercent: $percent,
            rate: $this->pricing->rate($unit->price->getCurrency()->getCurrencyCode(), $currency),
        );
    }

    /**
     * @param  array{customer_name?: string|null, customer_phone?: string|null, customer_id?: int|null, payment_reference?: string|null, notes?: string|null, sold_at?: CarbonImmutable|null}  $details
     */
    public function record(StockUnit $unit, Money $pricePaid, PaymentMethod $method, User $seller, array $details = []): Sale
    {
        $soldAt = $details['sold_at'] ?? now();

        if ($soldAt->isFuture()) {
            throw new SaleRuleViolation('The sale date can\'t be in the future.');
        }

        return DB::transaction(function () use ($unit, $pricePaid, $method, $seller, $details, $soldAt) {
            $unit = StockUnit::whereKey($unit->id)->lockForUpdate()->firstOrFail();

            if (! in_array($unit->status, [StockStatus::Received, StockStatus::Reserved], true)) {
                throw new SaleRuleViolation("{$unit->reference} isn't in the store, so it can't be sold.");
            }

            $quote = $this->quote($unit);

            if (! $pricePaid->getCurrency()->isEqualTo($quote->listed->getCurrency())) {
                throw new SaleRuleViolation("Enter the price in {$quote->listed->getCurrency()->getCurrencyCode()}.");
            }

            if (! $quote->accepts($pricePaid)) {
                throw new SaleRuleViolation('That\'s below the lowest price allowed ('.$this->pricing->present($quote->floor)['formatted'].').');
            }

            $sale = new Sale;
            $sale->forceFill([
                'stock_unit_id' => $unit->id,
                'location_id' => $unit->location_id,
                'sold_by' => $seller->id,
                'customer_id' => $details['customer_id'] ?? null,
                'customer_name' => $details['customer_name'] ?? null,
                'customer_phone' => $details['customer_phone'] ?? null,
                'list_price_amount' => $quote->listPrice->getMinorAmount()->toInt(),
                'list_price_currency' => $quote->listPrice->getCurrency()->getCurrencyCode(),
                'listed_amount' => $quote->listed->getMinorAmount()->toInt(),
                'floor_amount' => $quote->floor->getMinorAmount()->toInt(),
                'price_paid_amount' => $pricePaid->getMinorAmount()->toInt(),
                'currency' => $pricePaid->getCurrency()->getCurrencyCode(),
                'exchange_rate_id' => $quote->rate?->id,
                'rate' => $quote->rate?->rate,
                'floor_percent' => $quote->floorPercent,
                'payment_method' => $method,
                'payment_reference' => $method->takesReference() ? ($details['payment_reference'] ?? null) : null,
                'sold_at' => $soldAt,
                'notes' => $details['notes'] ?? null,
            ])->save();

            $unit->transitionTo(StockStatus::Sold);
            $unit->sold_at = $soldAt;
            $unit->save();

            return $sale;
        });
    }

    /**
     * Undoes a sale recorded by mistake or reversed by a return: the item goes
     * back into the store and the sale stays on record, marked void.
     */
    public function void(Sale $sale, User $by, string $reason): void
    {
        if (blank($reason)) {
            throw new SaleRuleViolation('Give a reason for voiding the sale.');
        }

        DB::transaction(function () use ($sale, $by, $reason) {
            $sale = Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($sale->isVoided()) {
                throw new SaleRuleViolation("{$sale->reference} is already void.");
            }

            $sale->forceFill([
                'voided_at' => now(),
                'voided_by' => $by->id,
                'void_reason' => $reason,
            ])->save();

            $unit = $sale->stockUnit;

            if ($unit->status === StockStatus::Sold) {
                $unit->transitionTo(StockStatus::Returned);
                $unit->transitionTo(StockStatus::Received);
                $unit->sold_at = null;
                $unit->save();
            }
        });
    }
}
