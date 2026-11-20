<?php

namespace App\Services\Reports;

use App\Enums\PaymentStatus;
use App\Enums\RequestStatus;
use App\Enums\ShipmentStatus;
use App\Enums\TransactionCategory;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\Sale;
use App\Models\Shipment;
use App\Models\StaffWage;
use App\Models\TransactionType;
use App\Reports\Statement;
use App\Reports\StatementLine;
use App\Reports\StatementSection;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Profit and loss for a date range. Sales, item costs and shipping come from
 * their own records; wages are prorated; everything else is ledger lines.
 */
class IncomeStatementReport
{
    public function __construct(
        private readonly ReportCurrency $currency,
        private readonly SaleFigures $figures,
        private readonly WageCalculator $wages,
    ) {}

    public function build(CarbonImmutable $from, CarbonImmutable $to): Statement
    {
        $f = $this->figures($from, $to);
        $zero = $this->currency->zero();

        $grossProfit = $f['sales']->minus($f['cost']);
        $otherIncome = array_reduce($f['income'], fn (Money $sum, StatementLine $line) => $sum->plus($line->amount), $zero);
        $expenses = array_reduce($f['expenses'], fn (Money $sum, StatementLine $line) => $sum->plus($line->amount), $zero);

        $sections = [
            new StatementSection('Sales', [
                new StatementLine('In-store sales', $f['sales'], trans_choice(':count item sold|:count items sold', $f['salesCount'])),
            ], new StatementLine('Total sales', $f['sales'])),
            new StatementSection('Cost of sales', [
                new StatementLine('Cost of items sold', $f['cost'], 'What the shop paid for them in the UK'),
            ], new StatementLine('Total cost of sales', $f['cost'])),
            StatementSection::total(new StatementLine('Gross profit', $grossProfit)),
            new StatementSection('Other income', $f['income'], new StatementLine('Total other income', $otherIncome)),
            new StatementSection('Expenses', $f['expenses'], new StatementLine('Total expenses', $expenses)),
            StatementSection::total(new StatementLine('Net profit', $grossProfit->plus($otherIncome)->minus($expenses))),
        ];

        return new Statement(
            title: 'Income statement',
            period: $from->format('j M Y').' to '.$to->format('j M Y'),
            currency: $this->currency->code(),
            sections: $sections,
            notes: $this->currency->warnings(),
        );
    }

    /**
     * Net profit for a range, used for retained earnings on the balance sheet.
     */
    public function netProfit(CarbonImmutable $from, CarbonImmutable $to): Money
    {
        $f = $this->figures($from, $to);
        $sum = fn (array $lines) => array_reduce($lines, fn (Money $sum, StatementLine $line) => $sum->plus($line->amount), $this->currency->zero());

        return $f['sales']->minus($f['cost'])->plus($sum($f['income']))->minus($sum($f['expenses']));
    }

    /**
     * @return array{sales: Money, salesCount: int, cost: Money, income: list<StatementLine>, expenses: list<StatementLine>}
     */
    private function figures(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $start = $from->startOfDay();
        $end = $to->endOfDay();
        $zero = $this->currency->zero();

        $sales = Sale::query()
            ->completed()
            ->whereBetween('sold_at', [$start, $end])
            ->with('stockUnit')
            ->get();

        $revenue = $sales->reduce(fn (Money $sum, Sale $sale) => $sum->plus($this->figures->revenue($sale)), $zero);
        $cost = $sales->reduce(fn (Money $sum, Sale $sale) => $sum->plus($this->figures->cost($sale)), $zero);

        $ledger = $this->ledgerTotals($start, $end);

        $income = [];
        $expenses = [];

        foreach ($this->types() as $type) {
            $amount = $ledger[$type->id] ?? null;

            if ($amount === null || $amount->isZero()) {
                continue;
            }

            if ($type->category === TransactionCategory::Income) {
                $income[] = new StatementLine($type->name, $amount);
            } else {
                $expenses[] = new StatementLine($type->name, $amount);
            }
        }

        $kept = $this->keptDeposits($start, $end);
        if (! $kept->isZero()) {
            array_unshift($income, new StatementLine('Kept deposits', $kept, 'Cancellation fees on requests cancelled after the item was found'));
        }

        [$shipping, $shipmentCount] = $this->shipping($start, $end);
        array_unshift(
            $expenses,
            new StatementLine('Shipping from the UK', $shipping, trans_choice(':count shipment|:count shipments', $shipmentCount)),
            new StatementLine('Staff wages', $this->wageCost($from, $to), 'Prorated from each wage\'s rate'),
        );

        return [
            'sales' => $revenue,
            'salesCount' => $sales->count(),
            'cost' => $cost,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    /**
     * @return array<int, Money>
     */
    private function ledgerTotals(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $totals = [];

        $entries = LedgerEntry::query()
            ->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])
            ->whereIn('transaction_type_id', $this->types()->modelKeys())
            ->get();

        foreach ($entries as $entry) {
            $amount = $this->currency->convert($entry->money, $entry->occurred_on);
            $totals[$entry->transaction_type_id] = ($totals[$entry->transaction_type_id] ?? $this->currency->zero())->plus($amount);
        }

        return $totals;
    }

    /**
     * @return Collection<int, TransactionType>
     */
    private function types(): Collection
    {
        return TransactionType::query()
            ->whereIn('category', [TransactionCategory::Income, TransactionCategory::Expense])
            ->where('derived_in_reports', false)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{0: Money, 1: int}
     */
    private function shipping(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $total = $this->currency->zero();
        $count = 0;

        $shipments = Shipment::query()
            ->where('status', '!=', ShipmentStatus::Draft)
            ->whereNotNull('dispatched_at')
            ->where(fn ($query) => $query
                ->whereBetween('dispatched_on', [$start->toDateString(), $end->toDateString()])
                ->orWhere(fn ($query) => $query->whereNull('dispatched_on')->whereBetween('dispatched_at', [$start, $end])))
            ->get();

        foreach ($shipments as $shipment) {
            $on = $shipment->dispatched_on ?? $shipment->dispatched_at ?? $end;
            $count++;

            if ($shipment->cost !== null) {
                $total = $total->plus($this->currency->convert($shipment->cost, $on));
            }
        }

        return [$total, $count];
    }

    /**
     * Deposit money not refunded on requests cancelled in the range.
     */
    private function keptDeposits(CarbonImmutable $start, CarbonImmutable $end): Money
    {
        $total = $this->currency->zero();

        $payments = Payment::query()
            ->where('payable_type', (new ProductRequest)->getMorphClass())
            ->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::Refunded])
            ->whereHasMorph('payable', [ProductRequest::class], fn ($query) => $query
                ->where('status', RequestStatus::Cancelled)
                ->whereBetween('cancelled_at', [$start, $end]))
            ->with('payable')
            ->get();

        foreach ($payments as $payment) {
            $kept = $payment->amount - ($payment->refunded_at !== null ? $payment->refunded_amount : 0);

            if ($kept > 0) {
                /** @var ProductRequest $request */
                $request = $payment->payable;
                $total = $total->plus($this->currency->convert(Money::ofMinor($kept, $payment->currency), $request->cancelled_at ?? $end));
            }
        }

        return $total;
    }

    private function wageCost(CarbonImmutable $from, CarbonImmutable $to): Money
    {
        $total = $this->currency->zero();

        $wages = StaffWage::query()
            ->whereDate('starts_on', '<=', $to)
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $from))
            ->get();

        foreach ($wages as $wage) {
            $total = $total->plus($this->currency->convert($this->wages->cost($wage, $from, $to), $to));
        }

        return $total;
    }
}
