<?php

namespace Tests\Feature\Reports;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\ShipmentStatus;
use App\Enums\StockStatus;
use App\Enums\WagePeriod;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\LedgerEntry;
use App\Models\Location;
use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\Sale;
use App\Models\Shipment;
use App\Models\StaffWage;
use App\Models\StockUnit;
use App\Models\TransactionType;
use App\Models\User;
use App\Reports\Statement;
use App\Services\Reports\BalanceSheetReport;
use App\Services\Reports\IncomeStatementReport;
use App\Services\SaleService;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialStatementsTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-06-15 12:00'));
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => '2026-01-01']);
        $this->staff = User::factory()->create()->assignRole(Role::Staff->value);
    }

    private function sell(int $costGbp, int $listGbp, int $paidGhs, string $soldAt): Sale
    {
        $unit = StockUnit::factory()->received()->create([
            'cost' => Money::of($costGbp, 'GBP'),
            'price' => Money::of($listGbp, 'GBP'),
            'purchased_at' => '2026-01-10',
        ]);

        return app(SaleService::class)->record($unit, Money::of($paidGhs, 'GHS'), PaymentMethod::Cash, $this->staff, [
            'sold_at' => CarbonImmutable::parse($soldAt),
        ]);
    }

    private function ledger(string $type, string $amount, string $on, string $currency = 'GHS'): void
    {
        LedgerEntry::create([
            'transaction_type_id' => TransactionType::where('name', $type)->value('id'),
            'occurred_on' => $on,
            'description' => $type,
            'money' => Money::of($amount, $currency),
        ]);
    }

    private function amount(Statement $statement, string $label): string
    {
        $line = $statement->find($label);
        $this->assertNotNull($line, "No [{$label}] line on the {$statement->title}.");

        return (string) $line->amount->getAmount();
    }

    private function june(): Statement
    {
        return app(IncomeStatementReport::class)->build(CarbonImmutable::parse('2026-06-01'), CarbonImmutable::parse('2026-06-30'));
    }

    public function test_the_income_statement_adds_up_sales_costs_shipping_wages_and_ledger_lines(): void
    {
        // Cost £60 × 15 = GH₵900; sold for GH₵1,400.
        $this->sell(60, 100, 1400, '2026-06-10 11:00');

        // Excluded: a voided sale and a sale in May.
        $voided = $this->sell(10, 50, 700, '2026-06-11 11:00');
        app(SaleService::class)->void($voided, $this->staff, 'Mistake');
        $this->sell(10, 50, 700, '2026-05-20 11:00');

        $shipment = Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
            'cost' => Money::of(20, 'GBP'),
            'dispatched_on' => '2026-06-02',
        ]);
        $shipment->forceFill(['status' => ShipmentStatus::InTransit, 'dispatched_at' => '2026-06-02 09:00'])->save();

        StaffWage::create([
            'name' => 'Ama',
            'rate' => Money::of(10, 'GHS'),
            'period' => WagePeriod::Daily,
            'starts_on' => '2026-06-21',
        ]);

        $this->ledger('Rent', '100', '2026-06-01');
        $this->ledger('Other Income', '25', '2026-06-05');
        $this->ledger('Rent', '999', '2026-05-01');

        $statement = $this->june();

        $this->assertSame('1400.00', $this->amount($statement, 'Total sales'));
        $this->assertSame('900.00', $this->amount($statement, 'Total cost of sales'));
        $this->assertSame('500.00', $this->amount($statement, 'Gross profit'));
        $this->assertSame('300.00', $this->amount($statement, 'Shipping from the UK'));
        $this->assertSame('100.00', $this->amount($statement, 'Staff wages'));
        $this->assertSame('100.00', $this->amount($statement, 'Rent'));
        $this->assertSame('25.00', $this->amount($statement, 'Total other income'));
        $this->assertSame('500.00', $this->amount($statement, 'Total expenses'));
        $this->assertSame('25.00', $this->amount($statement, 'Net profit'));
        $this->assertSame([], $statement->notes);
    }

    public function test_a_later_rate_change_does_not_change_past_margins(): void
    {
        $this->sell(60, 100, 1400, '2026-06-10 11:00');
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '20.000000', 'effective_at' => '2026-06-12']);

        $this->assertSame('900.00', $this->amount($this->june(), 'Total cost of sales'));
    }

    public function test_gbp_ledger_lines_convert_at_the_rate_on_the_day(): void
    {
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '16.000000', 'effective_at' => '2026-06-10']);
        $this->ledger('Rent', '10', '2026-06-05', 'GBP');
        $this->ledger('Rent', '10', '2026-06-12', 'GBP');

        $this->assertSame('310.00', $this->amount($this->june(), 'Rent'));
    }

    public function test_a_loss_is_negative_and_rendered_in_brackets(): void
    {
        $this->ledger('Rent', '1234.5', '2026-06-01');

        $net = $this->june()->find('Net profit');

        $this->assertNotNull($net);
        $this->assertTrue($net->isNegative());
        $this->assertSame('(1,234.50)', $net->formatted());
    }

    public function test_the_balance_sheet_values_stock_deposits_ledger_balances_and_retained_earnings(): void
    {
        $this->sell(60, 100, 1400, '2026-06-10 11:00');

        // Unsold stock at cost: £50 in store + £30 still in the UK = £80 × 15.
        StockUnit::factory()->received()->create(['cost' => Money::of(50, 'GBP'), 'purchased_at' => '2026-05-01']);
        StockUnit::factory()->status(StockStatus::Purchased)->create(['cost' => Money::of(30, 'GBP'), 'purchased_at' => '2026-06-01']);
        StockUnit::factory()->status(StockStatus::WrittenOff)->create(['cost' => Money::of(99, 'GBP'), 'purchased_at' => '2026-06-01']);

        $this->depositPaid(150);

        $this->ledger('Cash', '2000', '2026-02-01');
        $this->ledger('Long-Term Debts', '1000', '2026-02-01');
        $this->ledger('Long-Term Debts', '-400', '2026-05-01');
        $this->ledger('Common Stock', '1000', '2026-02-01');

        $sheet = app(BalanceSheetReport::class)->build(CarbonImmutable::parse('2026-06-30'));

        $this->assertSame('1200.00', $this->amount($sheet, 'Stock'));
        $this->assertSame('3200.00', $this->amount($sheet, 'Total assets'));
        $this->assertSame('150.00', $this->amount($sheet, 'Customer deposits held'));
        $this->assertSame('600.00', $this->amount($sheet, 'Long-Term Debts'));
        $this->assertSame('750.00', $this->amount($sheet, 'Total liabilities'));
        $this->assertSame('2450.00', $this->amount($sheet, 'Net assets'));
        $this->assertSame('500.00', $this->amount($sheet, 'Retained earnings'));
        $this->assertSame('1500.00', $this->amount($sheet, 'Total equity'));
        $this->assertSame('950.00', $this->amount($sheet, 'Not yet explained by the ledger'));
    }

    public function test_the_balance_sheet_on_an_earlier_date_ignores_later_activity(): void
    {
        $this->sell(60, 100, 1400, '2026-06-10 11:00');
        $this->ledger('Cash', '2000', '2026-06-01');

        $sheet = app(BalanceSheetReport::class)->build(CarbonImmutable::parse('2026-05-31'));

        // The item sold in June was still in stock at the end of May.
        $this->assertSame('900.00', $this->amount($sheet, 'Stock'));
        $this->assertNull($sheet->find('Cash'));
        $this->assertSame('0.00', $this->amount($sheet, 'Retained earnings'));
    }

    private function depositPaid(int $ghs): void
    {
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $request = ProductRequest::create([
            'customer_id' => $customer->id,
            'category_id' => Category::query()->value('id'),
            'condition_id' => Condition::query()->value('id'),
            'description' => 'iPhone 15',
            'deposit' => Money::of($ghs, 'GHS'),
        ]);
        $request->forceFill(['status' => 'open', 'paid_at' => '2026-06-03'])->save();

        $payment = new Payment;
        $payment->forceFill([
            'payable_type' => $request->getMorphClass(),
            'payable_id' => $request->id,
            'customer_id' => $customer->id,
            'provider' => 'fake',
            'reference' => 'PAY-1',
            'status' => PaymentStatus::Succeeded,
            'amount' => $ghs * 100,
            'currency' => 'GHS',
            'paid_at' => '2026-06-03 10:00',
        ])->save();
    }

    public function test_deposits_kept_on_cancelled_requests_count_as_other_income(): void
    {
        $this->depositPaid(200);
        $request = ProductRequest::firstOrFail();
        $request->forceFill(['status' => 'cancelled', 'cancelled_at' => '2026-06-12 10:00'])->save();
        Payment::firstOrFail()->forceFill([
            'status' => PaymentStatus::Refunded,
            'refunded_amount' => 15000,
            'refunded_at' => '2026-06-12 10:00',
        ])->save();

        $this->assertSame('50.00', $this->amount($this->june(), 'Kept deposits'));

        // No longer held for the customer.
        $sheet = app(BalanceSheetReport::class)->build(CarbonImmutable::parse('2026-06-30'));
        $this->assertSame('0.00', $this->amount($sheet, 'Customer deposits held'));
    }
}
