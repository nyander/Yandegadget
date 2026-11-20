<?php

namespace App\Services\Reports;

use App\Enums\PaymentStatus;
use App\Enums\StockStatus;
use App\Enums\TransactionCategory;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\StockUnit;
use App\Models\TransactionType;
use App\Reports\Statement;
use App\Reports\StatementLine;
use App\Reports\StatementSection;
use Brick\Money\Money;
use Carbon\CarbonImmutable;

/**
 * What the business owns and owes on a given day. Stock, customer deposits and
 * retained earnings are worked out from the shop's records; the rest is the
 * running total of ledger lines up to that day.
 */
class BalanceSheetReport
{
    /** Retained earnings count profit from this date, before any records existed. */
    private const string BEGINNING = '2000-01-01';

    public function __construct(
        private readonly ReportCurrency $currency,
        private readonly IncomeStatementReport $incomeStatement,
    ) {}

    public function build(CarbonImmutable $asOf): Statement
    {
        $asOf = $asOf->endOfDay();
        $ledger = $this->ledgerLines($asOf);

        $currentAssets = new StatementSection('Current assets', [
            ...$ledger[TransactionCategory::CurrentAsset->value],
            new StatementLine('Stock', $this->stockValue($asOf), 'Unsold items, at what they cost'),
        ]);
        $fixedAssets = new StatementSection('Fixed assets', $ledger[TransactionCategory::FixedAsset->value]);
        $currentLiabilities = new StatementSection('Current liabilities', [
            ...$ledger[TransactionCategory::CurrentLiability->value],
            new StatementLine('Customer deposits held', $this->depositsHeld($asOf), 'Paid for requests not yet collected or cancelled'),
        ]);
        $longTermLiabilities = new StatementSection('Long-term liabilities', $ledger[TransactionCategory::LongTermLiability->value]);
        $equity = new StatementSection('Equity', [
            ...$ledger[TransactionCategory::Equity->value],
            new StatementLine(
                'Retained earnings',
                $this->incomeStatement->netProfit(CarbonImmutable::parse(self::BEGINNING), $asOf),
                'Net profit to date',
            ),
        ]);

        [$currentAssets, $fixedAssets, $currentLiabilities, $longTermLiabilities, $equity] = array_map(
            fn (StatementSection $section) => new StatementSection(
                $section->title,
                $section->lines,
                new StatementLine('Total '.mb_strtolower((string) $section->title), $this->sum($section->lines)),
            ),
            [$currentAssets, $fixedAssets, $currentLiabilities, $longTermLiabilities, $equity],
        );

        $assets = $this->sum([$currentAssets->total, $fixedAssets->total]);
        $liabilities = $this->sum([$currentLiabilities->total, $longTermLiabilities->total]);
        $netAssets = $assets->minus($liabilities);
        $equityTotal = $this->sum([$equity->total]);

        $sections = [
            $currentAssets,
            $fixedAssets,
            StatementSection::total(new StatementLine('Total assets', $assets)),
            $currentLiabilities,
            $longTermLiabilities,
            StatementSection::total(new StatementLine('Total liabilities', $liabilities)),
            StatementSection::total(new StatementLine('Net assets', $netAssets)),
            $equity,
        ];

        $difference = $netAssets->minus($equityTotal);

        if (! $difference->isZero()) {
            $sections[] = StatementSection::total(new StatementLine(
                'Not yet explained by the ledger',
                $difference,
                'Net assets minus equity. Usually opening balances (cash, capital) that haven\'t been entered.',
            ));
        }

        return new Statement(
            title: 'Balance sheet',
            period: 'As at '.$asOf->format('j M Y'),
            currency: $this->currency->code(),
            sections: $sections,
            notes: $this->currency->warnings(),
        );
    }

    /**
     * Running totals per transaction type, grouped by category. Only types with a balance are listed.
     *
     * @return array<string, list<StatementLine>>
     */
    private function ledgerLines(CarbonImmutable $asOf): array
    {
        $lines = array_fill_keys(array_map(fn (TransactionCategory $c) => $c->value, TransactionCategory::cases()), []);

        $types = TransactionType::query()
            ->where('derived_in_reports', false)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (TransactionType $type) => $type->category->isBalanceSheet());

        $totals = [];

        $entries = LedgerEntry::query()
            ->whereIn('transaction_type_id', $types->modelKeys())
            ->whereDate('occurred_on', '<=', $asOf)
            ->get();

        foreach ($entries as $entry) {
            // Balances are valued at the rate on the balance sheet date.
            $amount = $this->currency->convert($entry->money, $asOf);
            $totals[$entry->transaction_type_id] = ($totals[$entry->transaction_type_id] ?? $this->currency->zero())->plus($amount);
        }

        foreach ($types as $type) {
            $amount = $totals[$type->id] ?? null;

            if ($amount !== null && ! $amount->isZero()) {
                $lines[$type->category->value][] = new StatementLine($type->name, $amount);
            }
        }

        return $lines;
    }

    private function stockValue(CarbonImmutable $asOf): Money
    {
        $total = $this->currency->zero();

        StockUnit::query()
            ->where('status', '!=', StockStatus::WrittenOff)
            ->whereDate('purchased_at', '<=', $asOf)
            ->where(fn ($query) => $query->whereNull('sold_at')->orWhere('sold_at', '>', $asOf))
            ->select(['id', 'cost_amount', 'cost_currency'])
            ->chunkById(500, function ($units) use (&$total, $asOf) {
                foreach ($units as $unit) {
                    /** @var StockUnit $unit */
                    $total = $total->plus($this->currency->convert($unit->cost, $asOf));
                }
            });

        return $total;
    }

    private function depositsHeld(CarbonImmutable $asOf): Money
    {
        $total = $this->currency->zero();

        $payments = Payment::query()
            ->where('payable_type', (new ProductRequest)->getMorphClass())
            ->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::Refunded])
            ->where('paid_at', '<=', $asOf)
            ->whereHasMorph('payable', [ProductRequest::class], fn ($query) => $query
                ->where(fn ($query) => $query->whereNull('fulfilled_at')->orWhere('fulfilled_at', '>', $asOf))
                ->where(fn ($query) => $query->whereNull('cancelled_at')->orWhere('cancelled_at', '>', $asOf)))
            ->get();

        foreach ($payments as $payment) {
            $held = $payment->amount;

            if ($payment->refunded_at !== null && $payment->refunded_at->lessThanOrEqualTo($asOf)) {
                $held -= $payment->refunded_amount;
            }

            $total = $total->plus($this->currency->convert(Money::ofMinor($held, $payment->currency), $asOf));
        }

        return $total;
    }

    /**
     * @param  list<StatementLine|null>  $lines
     */
    private function sum(array $lines): Money
    {
        return array_reduce(
            $lines,
            fn (Money $sum, ?StatementLine $line) => $line === null ? $sum : $sum->plus($line->amount),
            $this->currency->zero(),
        );
    }
}
