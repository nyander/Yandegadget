<?php

namespace Tests\Feature\Sales;

use App\Enums\Role;
use App\Enums\StockStatus;
use App\Models\ExchangeRate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StaffSaleTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private StockUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $legon = Location::where('code', 'ACC-LEGON')->firstOrFail();
        $this->staff = User::factory()->create(['location_id' => $legon->id])->assignRole(Role::Staff->value);
        $this->unit = StockUnit::factory()
            ->for(Product::factory()->create(['name' => 'PlayStation 5 Disc']))
            ->received()->priced(340)->create(['location_id' => $legon->id]);
    }

    private function validSale(array $overrides = []): array
    {
        return [
            'price' => '4000',
            'payment_method' => 'mobile_money',
            'payment_reference' => 'MOMO-9',
            'sold_on' => today()->toDateString(),
            ...$overrides,
        ];
    }

    public function test_staff_find_items_in_store_by_name_or_reference(): void
    {
        StockUnit::factory()->status(StockStatus::InTransit)->create();

        $this->actingAs($this->staff)
            ->get(route('staff.sell.index', ['q' => 'playstation']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/sell/index')
                ->has('units', 1)
                ->where('units.0.reference', $this->unit->reference)
                ->where('units.0.listed.amount', 510000));

        $this->actingAs($this->staff)
            ->get(route('staff.sell.index', ['q' => $this->unit->reference]))
            ->assertInertia(fn (Assert $page) => $page->has('units', 1));
    }

    public function test_the_sale_screen_shows_listed_and_floor_prices_but_not_cost(): void
    {
        $this->actingAs($this->staff)
            ->get(route('staff.sell.create', $this->unit))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/sell/create')
                ->where('pricing.listed.amount', 510000)
                ->where('pricing.floor.amount', 357000)
                ->where('pricing.floor_percent', 70)
                ->missing('unit.cost')
                ->where('problem', null));
    }

    public function test_recording_a_sale_shows_the_receipt(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.sell.store', $this->unit), $this->validSale());

        $sale = Sale::firstOrFail();
        $response->assertRedirect(route('staff.sales.show', $sale));
        $this->assertSame(400000, $sale->price_paid_amount);
        $this->assertSame($this->staff->id, $sale->sold_by);

        $this->actingAs($this->staff)
            ->get(route('staff.sales.show', $sale))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/sales/show')
                ->where('sale.price_paid.amount', 400000)
                ->where('sale.discount_percent', 22)
                ->where('sale.payment_reference', 'MOMO-9'));

        $this->actingAs($this->staff)
            ->get(route('staff.sell.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('today.count', 1)
                ->where('today.total.amount', 400000)
                ->has('units', 0));
    }

    public function test_below_the_floor_is_refused_with_a_message(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.sell.store', $this->unit), $this->validSale(['price' => '3000']))
            ->assertSessionHasErrors('price');

        $this->assertSame(0, Sale::count());
    }

    public function test_input_is_validated(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.sell.store', $this->unit), $this->validSale([
                'price' => '12.345',
                'payment_method' => 'bitcoin',
                'sold_on' => today()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['price', 'payment_method', 'sold_on']);
    }

    public function test_backdated_sales_keep_the_chosen_date(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.sell.store', $this->unit), $this->validSale(['sold_on' => today()->subDays(2)->toDateString()]));

        $this->assertTrue(Sale::firstOrFail()->sold_at->isSameDay(today()->subDays(2)));
    }

    public function test_only_staff_at_the_items_store_can_sell_it(): void
    {
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $this->actingAs($customer)->get(route('staff.sell.index'))->assertForbidden();

        $elsewhere = User::factory()->create(['location_id' => Location::factory()->create()->id])->assignRole(Role::Staff->value);
        $this->actingAs($elsewhere)->get(route('staff.sell.create', $this->unit))->assertForbidden();
        $this->actingAs($elsewhere)->post(route('staff.sell.store', $this->unit), $this->validSale())->assertForbidden();
    }

    public function test_sold_items_cannot_be_opened_for_sale_again(): void
    {
        $this->actingAs($this->staff)->post(route('staff.sell.store', $this->unit), $this->validSale());

        $this->actingAs($this->staff)->get(route('staff.sell.create', $this->unit))->assertForbidden();
    }

    public function test_the_sale_screen_explains_a_missing_rate(): void
    {
        ExchangeRate::query()->delete();

        $this->actingAs($this->staff)
            ->get(route('staff.sell.create', $this->unit))
            ->assertInertia(fn (Assert $page) => $page
                ->where('pricing', null)
                ->where('problem', fn ($message) => str_contains($message, 'exchange rate')));
    }
}
