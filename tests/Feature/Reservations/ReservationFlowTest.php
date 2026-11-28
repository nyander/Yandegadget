<?php

namespace Tests\Feature\Reservations;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationPayment;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Exceptions\SaleRuleViolation;
use App\Models\ExchangeRate;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\NewReservation;
use App\Notifications\ReservationUpdated;
use App\Services\ReservationService;
use App\Services\SaleService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $staff;

    private Product $product;

    private StockUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.payments.driver' => 'fake']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $this->customer = User::factory()->create()->assignRole(Role::Customer->value);
        $this->staff = User::factory()->create()->assignRole(Role::Staff->value);
        User::factory()->create()->assignRole(Role::Admin->value);
        $this->product = Product::factory()->create(['name' => 'iPhone 13 128GB']);
        // £340 × 15 = GH₵5,100
        $this->unit = StockUnit::factory()->for($this->product)->received()->create(['price' => Money::of(340, 'GBP')]);
    }

    /**
     * Reserves through the shop and approves the test mobile money payment.
     */
    private function reserveAndPay(string $option, ?User $as = null, ?StockUnit $unit = null): Reservation
    {
        $unit ??= $this->unit;
        $checkout = $this->actingAs($as ?? $this->customer)
            ->post(route('reservations.store', ['product' => $this->product->slug, 'reference' => $unit->reference]), ['payment_option' => $option])
            ->headers->get('Location');
        $payment = Payment::latest('id')->firstOrFail();
        $this->assertSame(route('payments.test', $payment), $checkout);

        $this->post(route('payments.test.decide', $payment), ['outcome' => 'approve', 'channel' => 'mobile_money', 'network' => 'mtn']);
        $this->get(route('payments.callback', ['reference' => $payment->reference]));

        return Reservation::latest('id')->firstOrFail();
    }

    public function test_the_shop_shows_a_reserve_button_and_the_choice_of_full_or_deposit(): void
    {
        $this->get(route('shop.show', $this->product))->assertInertia(fn (Assert $page) => $page
            ->where('product.units.0.reserve_url', route('reservations.create', ['product' => $this->product->slug, 'reference' => $this->unit->reference])));

        $this->actingAs($this->customer)
            ->get(route('reservations.create', ['product' => $this->product->slug, 'reference' => $this->unit->reference]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('reservations/create')
                ->where('price.amount', 510000)
                // 20% of GH₵5,100, in whole cedis
                ->where('deposit.amount', 102000)
                ->where('depositPercent', 20)
                ->where('days', 3));
    }

    public function test_paying_in_full_reserves_the_item_and_the_till_hands_it_over(): void
    {
        Notification::fake();

        $reservation = $this->reserveAndPay('full');

        $this->assertSame(ReservationStatus::Reserved, $reservation->status);
        $this->assertSame(ReservationPayment::Full, $reservation->payment_option);
        $this->assertSame(510000, $reservation->due->getMinorAmount()->toInt());
        $this->assertTrue($reservation->collect_by?->isSameDay(now()->addDays(3)));
        $this->assertSame(StockStatus::Reserved, $this->unit->fresh()?->status);
        $this->assertSame('mobile_money', $reservation->paidPayment()?->channel);
        Notification::assertSentTo($this->customer, ReservationUpdated::class);
        Notification::assertSentTo($this->staff, NewReservation::class);

        // Nobody else can reserve it now.
        $other = User::factory()->create()->assignRole(Role::Customer->value);
        $this->actingAs($other)
            ->get(route('reservations.create', ['product' => $this->product->slug, 'reference' => $this->unit->reference]))
            ->assertRedirect(route('shop.show', $this->product));

        // In store: the full price was paid online, so that's the sale.
        $sale = app(SaleService::class)->record($this->unit->fresh() ?? $this->unit, Money::of(5100, 'GHS'), PaymentMethod::MobileMoney, $this->staff);

        $this->assertSame($reservation->id, $sale->reservation_id);
        $this->assertSame(510000, $sale->deposit_applied_amount);
        $this->assertSame($this->customer->id, $sale->customer_id);
        $this->assertSame(ReservationStatus::Collected, $reservation->fresh()?->status);
    }

    public function test_a_deposit_comes_off_the_price_agreed_in_store(): void
    {
        $reservation = $this->reserveAndPay('deposit');

        $this->assertSame(102000, $reservation->due->getMinorAmount()->toInt());
        $this->assertSame(408000, $reservation->balance()->getMinorAmount()->toInt());

        $sales = app(SaleService::class);

        $this->expectException(SaleRuleViolation::class);
        $sales->record($this->unit->fresh() ?? $this->unit, Money::of(900, 'GHS'), PaymentMethod::Cash, $this->staff);
    }

    public function test_not_collected_in_time_means_a_full_refund(): void
    {
        $reservation = $this->reserveAndPay('deposit');
        Notification::fake();

        $this->travel(4)->days();
        $this->artisan('reservations:release')->expectsOutputToContain('Released 1 reservation')->assertSuccessful();

        $reservation->refresh();
        $this->assertSame(ReservationStatus::Cancelled, $reservation->status);
        $this->assertSame('not_collected', $reservation->cancelled_by);
        $payment = $reservation->paidPayment();
        $this->assertSame(PaymentStatus::Refunded, $payment?->status);
        $this->assertSame(102000, $payment?->refunded_amount);
        $this->assertSame(StockStatus::Received, $this->unit->fresh()?->status);
        Notification::assertSentTo($this->customer, ReservationUpdated::class);
    }

    public function test_an_unpaid_hold_runs_out_and_a_declined_payment_can_be_retried(): void
    {
        $checkout = $this->actingAs($this->customer)
            ->post(route('reservations.store', ['product' => $this->product->slug, 'reference' => $this->unit->reference]), ['payment_option' => 'full'])
            ->headers->get('Location');
        $payment = Payment::latest('id')->firstOrFail();
        $this->assertNotNull($checkout);

        $this->post(route('payments.test.decide', $payment), ['outcome' => 'decline', 'channel' => 'mobile_money']);
        $this->get(route('payments.callback', ['reference' => $payment->reference]));

        $reservation = Reservation::firstOrFail();
        $this->assertSame(PaymentStatus::Failed, $payment->fresh()?->status);
        $this->assertSame(ReservationStatus::AwaitingPayment, $reservation->status);
        $this->assertSame(StockStatus::Reserved, $this->unit->fresh()?->status);

        // Staff can't sell it while it's being paid for.
        try {
            app(SaleService::class)->record($this->unit->fresh() ?? $this->unit, Money::of(5100, 'GHS'), PaymentMethod::Cash, $this->staff);
            $this->fail('Sold an item that was being paid for online.');
        } catch (SaleRuleViolation $e) {
            $this->assertStringContainsString('being paid for online', $e->getMessage());
        }

        $this->actingAs($this->customer)->post(route('reservations.pay', $reservation))->assertRedirect();
        $this->assertSame(2, $reservation->payments()->count());

        // Never paid: after the hold, it's back on sale with nothing to refund.
        $this->travel(ReservationService::HOLD_MINUTES + 1)->minutes();
        app(ReservationService::class)->releaseExpired();

        $this->assertSame(ReservationStatus::Cancelled, $reservation->fresh()?->status);
        $this->assertSame(StockStatus::Received, $this->unit->fresh()?->status);
    }

    public function test_customers_cancel_their_own_reservation_for_a_full_refund(): void
    {
        $reservation = $this->reserveAndPay('full');
        $other = User::factory()->create()->assignRole(Role::Customer->value);

        $this->actingAs($other)->get(route('reservations.show', $reservation))->assertForbidden();
        $this->actingAs($other)->post(route('reservations.cancel', $reservation))->assertForbidden();

        $this->actingAs($this->customer)->get(route('reservations.show', $reservation))
            ->assertInertia(fn (Assert $page) => $page->component('reservations/show')->where('canCancel', true)->where('reservation.status', 'reserved'));

        $this->actingAs($this->customer)->post(route('reservations.cancel', $reservation))->assertRedirect(route('reservations.show', $reservation));

        $this->assertSame(ReservationStatus::Cancelled, $reservation->fresh()?->status);
        $this->assertSame(510000, $reservation->paidPayment()?->refunded_amount);
        $this->assertSame(StockStatus::Received, $this->unit->fresh()?->status);
    }

    public function test_voiding_the_sale_keeps_the_item_for_the_customer_again(): void
    {
        $reservation = $this->reserveAndPay('full');
        $sales = app(SaleService::class);
        $sale = $sales->record($this->unit->fresh() ?? $this->unit, Money::of(5100, 'GHS'), PaymentMethod::MobileMoney, $this->staff);

        $sales->void($sale, User::factory()->create()->assignRole(Role::Admin->value), 'Wrong item scanned');

        $this->assertSame(ReservationStatus::Reserved, $reservation->fresh()?->status);
        $this->assertSame(StockStatus::Reserved, $this->unit->fresh()?->status);
    }
}
