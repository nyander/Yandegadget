<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\WagePeriod;
use App\Filament\Pages\Reports\BalanceSheet;
use App\Filament\Pages\Reports\IncomeStatement;
use App\Filament\Resources\LedgerEntries\Pages\ManageLedgerEntries;
use App\Filament\Resources\StaffWages\Pages\ManageStaffWages;
use App\Filament\Widgets\SalesBreakdown;
use App\Filament\Widgets\SalesOverview;
use App\Filament\Widgets\SalesTrendChart;
use App\Filament\Widgets\StockAge;
use App\Models\ExchangeRate;
use App\Models\LedgerEntry;
use App\Models\StaffWage;
use App\Models\TransactionType;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('admin');
    }

    public function test_the_dashboard_shows_sales_and_stock(): void
    {
        $this->get('/admin')->assertOk();

        // Widgets load lazily, so render each one.
        Livewire::test(SalesOverview::class, ['pageFilters' => ['from' => '2026-01-01', 'to' => '2026-01-31']])
            ->assertSee('Gross profit')
            ->assertSee('1 Jan – 31 Jan 2026');
        Livewire::test(SalesTrendChart::class)->assertOk();
        Livewire::test(StockAge::class)->assertSee('90 days or more');
        Livewire::test(SalesBreakdown::class)->assertSee('No sales in this period');
    }

    public function test_statement_pages_render_and_download(): void
    {
        $this->get('/admin/income-statement')->assertOk()->assertSee('Net profit');
        $this->get('/admin/balance-sheet')->assertOk()->assertSee('Total assets');

        Livewire::test(IncomeStatement::class)
            ->fillForm(['from' => '2026-01-01', 'to' => '2026-01-31'])
            ->assertSee('1 Jan 2026 to 31 Jan 2026')
            ->callAction('csv')
            ->assertFileDownloaded('nya-income-statement-1-jan-2026-to-31-jan-2026.csv');

        Livewire::test(BalanceSheet::class)
            ->callAction('pdf')
            ->assertFileDownloaded(contentType: 'application/pdf');
    }

    public function test_admin_records_ledger_entries_and_wages(): void
    {
        $rent = TransactionType::where('name', 'Rent')->firstOrFail();

        Livewire::test(ManageLedgerEntries::class)
            ->callAction('create', [
                'occurred_on' => now()->toDateString(),
                'transaction_type_id' => $rent->id,
                'description' => 'Shop rent, June',
                'currency' => 'GHS',
                'money' => '-250.50',
            ])
            ->assertHasNoFormErrors();

        $entry = LedgerEntry::firstOrFail();
        $this->assertSame(-25050, $entry->amount);
        $this->assertSame('GHS', $entry->currency);
        $this->assertSame($this->admin->id, $entry->recorded_by);

        Livewire::test(ManageStaffWages::class)
            ->callAction('create', [
                'name' => 'Kwame',
                'rate_currency' => 'GHS',
                'rate' => '1800',
                'period' => WagePeriod::Monthly->value,
                'starts_on' => '2026-01-01',
            ])
            ->assertHasNoFormErrors();

        $wage = StaffWage::firstOrFail();
        $this->assertSame(180000, $wage->rate_amount);
        $this->assertSame(WagePeriod::Monthly, $wage->period);
    }

    public function test_staff_cannot_open_reports_or_the_ledger(): void
    {
        $this->actingAs(User::factory()->create()->assignRole(Role::Staff->value));

        $this->get('/admin/income-statement')->assertForbidden();
        $this->get('/admin/ledger-entries')->assertForbidden();
    }
}
