<?php

namespace Tests\Feature\Reports;

use App\Enums\BuyingListStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Resources\BuyingListItems\Pages\ManageBuyingListItems;
use App\Filament\Widgets\SalesBreakdown;
use App\Filament\Widgets\StockAge;
use App\Models\BuyingListItem;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\Reports\BuyingInsights;
use App\Services\SaleService;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BuyingInsightsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $phones;

    private Condition $grade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-06-30 12:00'));
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '10.000000', 'effective_at' => '2026-01-01']);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->phones = Category::where('slug', 'phones')->firstOrFail();
        $this->grade = Condition::orderBy('sort_order')->firstOrFail();
    }

    /**
     * Sells $count phones of one product in June: bought for £$cost, sold for GH₵$paid, 6 days after arriving.
     */
    private function sellPhones(int $count, int $cost = 50, int $paid = 1000, ?Product $product = null): Product
    {
        $product ??= Product::factory()->create(['category_id' => $this->phones->id, 'name' => 'iPhone 13']);

        for ($i = 0; $i < $count; $i++) {
            $unit = StockUnit::factory()->received()->create([
                'product_id' => $product->id,
                'condition_id' => $this->grade->id,
                'cost' => Money::of($cost, 'GBP'),
                'price' => Money::of($paid / 10, 'GBP'),
            ]);
            $unit->forceFill(['received_at' => CarbonImmutable::parse('2026-06-04')])->save();
            app(SaleService::class)->record($unit, Money::of($paid, 'GHS'), PaymentMethod::Cash, $this->admin, ['sold_at' => CarbonImmutable::parse('2026-06-10')]);
        }

        return $product;
    }

    /** @return array<string, mixed> */
    private function phonesInsights(): array
    {
        return app(BuyingInsights::class)->forGroup(
            CarbonImmutable::parse('2026-06-01'),
            CarbonImmutable::parse('2026-06-30'),
            'category',
            $this->phones->name,
        );
    }

    public function test_a_fast_seller_with_little_stock_is_worth_buying_more_of(): void
    {
        $product = $this->sellPhones(3);
        StockUnit::factory()->received()->create(['product_id' => $product->id, 'condition_id' => $this->grade->id]);

        $insights = $this->phonesInsights();

        $this->assertSame(3, $insights['units']);
        $this->assertSame(3.0, $insights['perMonth']);
        $this->assertSame(1, $insights['inStore']);
        $this->assertSame(6.0, $insights['averageDays']);
        $this->assertSame('buy', $insights['suggestion']['tone']);
        // Two months at 3 a month is 6; 1 in store, so 5 more.
        $this->assertSame(5, $insights['suggestion']['quantity']);

        $this->assertSame('iPhone 13', $insights['bestSellers'][0]['product']);
        $this->assertSame('iPhone 13', $insights['prefill']['description']);
        $this->assertSame($this->phones->id, $insights['prefill']['category_id']);
        $this->assertSame($this->grade->id, $insights['prefill']['condition_id']);
        $this->assertSame(5, $insights['prefill']['quantity']);
        $this->assertTrue(Money::of(50, 'GBP')->isEqualTo($insights['prefill']['target_cost']));
    }

    public function test_plenty_of_stock_means_hold_off(): void
    {
        $product = $this->sellPhones(1);
        StockUnit::factory()->count(2)->received()->create(['product_id' => $product->id]);
        StockUnit::factory()->count(2)->status(StockStatus::InTransit)->create(['product_id' => $product->id]);

        $insights = $this->phonesInsights();

        $this->assertSame(2, $insights['onTheWay']);
        $this->assertSame('hold', $insights['suggestion']['tone']);
        $this->assertStringContainsString('4 months', $insights['suggestion']['text']);
    }

    public function test_a_thin_margin_says_buy_carefully(): void
    {
        // £95 at 10 GHS/GBP costs GH₵950; sold for GH₵1,000 is a 5% margin.
        $this->sellPhones(2, cost: 95, paid: 1000);

        $this->assertSame('careful', $this->phonesInsights()['suggestion']['tone']);
    }

    public function test_stock_age_details_list_the_items_in_the_band_oldest_first(): void
    {
        foreach ([5, 20, 95] as $days) {
            StockUnit::factory()->received()->create(['cost' => Money::of(10, 'GBP')])
                ->forceFill(['received_at' => now()->subDays($days)])->save();
        }

        $recent = app(BuyingInsights::class)->forAgeBand(0);
        $this->assertSame(2, $recent['units']);
        $this->assertSame([20, 5], array_column($recent['items'], 'days'));

        $old = app(BuyingInsights::class)->forAgeBand(3);
        $this->assertSame(1, $old['units']);
        $this->assertStringContainsString('lower price', (string) $old['advice']);
    }

    public function test_the_owner_opens_a_row_and_adds_its_best_seller_to_the_buying_list(): void
    {
        $this->sellPhones(2);
        $this->actingAs($this->admin);
        $filters = ['pageFilters' => ['from' => '2026-06-01', 'to' => '2026-06-30']];

        Livewire::test(SalesBreakdown::class, $filters)
            ->assertSee('click a row for details')
            ->mountAction('details', ['by' => 'category', 'label' => $this->phones->name])
            ->assertActionMounted('details')
            ->assertMountedActionModalSee(['Worth buying more', 'Best sellers', 'iPhone 13']);

        // As in the browser: open the row, press the footer button, change the quantity, save.
        Livewire::test(SalesBreakdown::class, $filters)
            ->call('mountAction', 'details', ['by' => 'category', 'label' => $this->phones->name])
            ->call('mountAction', 'addToBuyingList')
            ->assertSet('mountedActions.1.data.description', 'iPhone 13')
            ->set('mountedActions.1.data.quantity', 3)
            ->call('callMountedAction')
            ->assertHasNoErrors();

        $item = BuyingListItem::sole();
        $this->assertSame('iPhone 13', $item->description);
        $this->assertSame(3, $item->quantity);
        $this->assertSame($this->phones->id, $item->category_id);
        $this->assertTrue(Money::of(50, 'GBP')->isEqualTo($item->target_cost));
        $this->assertSame(BuyingListStatus::ToBuy, $item->status);
        $this->assertSame('What sold · '.$this->phones->name, $item->source);
        $this->assertSame($this->admin->id, $item->added_by);
    }

    public function test_stock_age_rows_open_their_items(): void
    {
        StockUnit::factory()->received()->create()->forceFill(['received_at' => now()->subDays(100)])->save();
        $this->actingAs($this->admin);

        Livewire::test(StockAge::class)
            ->mountAction('details', ['band' => 3])
            ->assertMountedActionModalSee('three months or more');
    }

    public function test_items_are_ticked_off_the_buying_list(): void
    {
        $item = BuyingListItem::create(['description' => 'PS5 Slim', 'quantity' => 2]);
        $this->actingAs($this->admin);

        Livewire::test(ManageBuyingListItems::class)
            ->assertCanSeeTableRecords([$item])
            ->callAction(TestAction::make('bought')->table($item));

        $this->assertSame(BuyingListStatus::Bought, $item->refresh()->status);
        $this->assertNotNull($item->bought_at);
    }

    public function test_staff_cannot_use_the_buying_list(): void
    {
        $this->actingAs(User::factory()->create()->assignRole(Role::Staff->value))
            ->get('/admin/buying-list')
            ->assertForbidden();
    }
}
