<?php

namespace App\Services\Reports;

use App\Models\Sale;
use Brick\Money\Money;

/**
 * Revenue and cost of one sale in the report currency. The item's GBP cost
 * is converted at the rate copied onto the sale, so margins stay as they were.
 */
class SaleFigures
{
    public function __construct(private readonly ReportCurrency $currency) {}

    public function revenue(Sale $sale): Money
    {
        return $this->currency->convert($sale->price_paid, $sale->sold_at);
    }

    public function cost(Sale $sale): Money
    {
        $cost = $sale->stockUnit->cost;
        $costCurrency = $cost->getCurrency()->getCurrencyCode();

        if ($costCurrency !== $sale->currency
            && $sale->rate !== null
            && $costCurrency === $sale->list_price_currency) {
            $cost = ReportCurrency::multiply($cost, $sale->rate, $sale->currency);
        }

        return $this->currency->convert($cost, $sale->sold_at);
    }
}
