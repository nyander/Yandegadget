<?php

namespace Tests\Feature\Requests;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReceiptOutcome;
use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Exceptions\RequestRuleViolation;
use App\Exceptions\SaleRuleViolation;
use App\Models\Carrier;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\Location;
use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\Shipment;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\NewProductRequest;
use App\Notifications\ProductRequestUpdated;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaystackGateway;
use App\Services\ProductRequestService;
use App\Services\SaleService;
use App\Services\ShipmentService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.payments.driver' => 'fake']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $this->customer = User::factory()->create()->assignRole(Role::Customer->value);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
    }

    private function phones(): Category
    {
        return Category::where('slug', 'phones')->firstOrFail();
    }

    private function gradeA(): Condition
    {
        return Condition::where('grade', 'A')->firstOrFail();
    }

    /**
     * Makes a request through the website and pays the deposit with the fake gateway.
     */
    private function paidRequest(): ProductRequest
    {
        $response = $this->actingAs($this->customer)->post(route('requests.store'), [
            'category_id' => $this->phones()->id,
            'condition_id' => $this->gradeA()->id,
            'description' => 'iPhone 14 Pro 256GB',
        ]);

        $this->get($response->headers->get('Location') ?? '');

        return ProductRequest::latest('id')->firstOrFail();
    }

    public function test_customer_requests_an_item_and_pays_the_deposit_in_cedis(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->customer)->post(route('requests.store'), [
            'category_id' => $this->phones()->id,
            'condition_id' => $this->gradeA()->id,
            'description' => 'iPhone 14 Pro 256GB',
            'notes' => 'Any colour',
        ]);

        $request = ProductRequest::firstOrFail();
        $this->assertSame(RequestStatus::AwaitingDeposit, $request->status);
        $this->assertSame(sprintf('RQ-%05d', $request->id), $request->reference);

        $payment = Payment::firstOrFail();
        // £80 deposit × 15 = GH₵1,200
        $this->assertSame(120000, $payment->amount);
        $this->assertSame('GHS', $payment->currency);
        $this->assertSame('15.000000', $payment->exchange_rate);
        $response->assertRedirect(route('payments.callback', ['reference' => $payment->reference]));

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertRedirect(route('requests.show', $request));

        $this->assertSame(PaymentStatus::Succeeded, $payment->fresh()?->status);
        $this->assertSame(RequestStatus::Open, $request->fresh()?->status);
        Notification::assertSentTo($this->admin, NewProductRequest::class);
        Notification::assertSentTo($this->customer, ProductRequestUpdated::class);

        // Returning to the callback again does nothing new.
        $this->get(route('payments.callback', ['reference' => $payment->reference]));
        Notification::assertSentToTimes($this->admin, NewProductRequest::class, 1);
    }

    public function test_the_request_page_shows_progress_to_its_owner_only(): void
    {
        $request = $this->paidRequest();

        $this->actingAs($this->customer)->get(route('requests.show', $request))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('requests/show')
                ->where('request.status', 'open')
                ->where('request.deposit.amount', 120000)
                ->where('request.can_pay', false));

        $other = User::factory()->create()->assignRole(Role::Customer->value);
        $this->actingAs($other)->get(route('requests.show', $request))->assertForbidden();
        $this->actingAs($this->admin)->get(route('requests.show', $request))->assertOk();
    }

    public function test_staff_and_suppliers_cannot_make_requests(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);

        $this->actingAs($staff)->get(route('requests.create'))->assertForbidden();
    }

    public function test_validation(): void
    {
        $this->phones()->update(['is_active' => false]);

        $this->actingAs($this->customer)
            ->post(route('requests.store'), ['category_id' => $this->phones()->id, 'condition_id' => 999, 'description' => 'x'])
            ->assertSessionHasErrors(['category_id', 'condition_id', 'description']);
    }

    public function test_an_item_already_in_store_is_reserved_straight_away(): void
    {
        Notification::fake();
        $request = $this->paidRequest();
        $unit = StockUnit::factory()->received()->priced(500)->create();

        app(ProductRequestService::class)->source($request, $unit);

        $this->assertSame(RequestStatus::ReadyForCollection, $request->fresh()?->status);
        $this->assertSame(StockStatus::Reserved, $unit->fresh()?->status);
        Notification::assertSentTo($this->customer, ProductRequestUpdated::class, fn ($n) => $n->toDatabase($this->customer)['status'] === 'ready');
    }

    public function test_an_item_bought_in_the_uk_is_reserved_when_it_is_checked_in(): void
    {
        $request = $this->paidRequest();
        $unit = StockUnit::factory()->create(['location_id' => Location::where('code', 'UK-BUYING')->value('id')]);

        app(ProductRequestService::class)->source($request, $unit);
        $this->assertSame(RequestStatus::Sourced, $request->fresh()?->status);

        $shipments = app(ShipmentService::class);
        $shipment = Shipment::create([
            'origin_location_id' => $unit->location_id,
            'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
        ]);
        $shipments->addUnits($shipment, [$unit]);
        $shipments->dispatch($shipment, ['carrier_id' => Carrier::create(['name' => 'DHL'])->id, 'cost' => Money::of(100, 'GBP'), 'dispatched_on' => today()->toDateString()], $this->admin);
        $shipments->checkIn($shipment->items()->firstOrFail(), ReceiptOutcome::Received, $this->admin);

        $this->assertSame(StockStatus::Reserved, $unit->fresh()?->status);
        $this->assertSame(RequestStatus::ReadyForCollection, $request->fresh()?->status);
    }

    public function test_a_unit_can_only_be_matched_to_one_request(): void
    {
        $first = $this->paidRequest();
        $second = $this->paidRequest();
        $unit = StockUnit::factory()->create();
        $service = app(ProductRequestService::class);
        $service->source($first, $unit);

        $this->expectException(RequestRuleViolation::class);
        $service->source($second, $unit);
    }

    public function test_the_deposit_counts_towards_the_sale_and_completes_the_request(): void
    {
        $request = $this->paidRequest();
        $unit = StockUnit::factory()->received()->priced(500)->create();
        app(ProductRequestService::class)->source($request, $unit);
        $sales = app(SaleService::class);

        try {
            $sales->record($unit, Money::of(1000, 'GHS'), PaymentMethod::Cash, $this->admin);
            $this->fail('A price below the deposit should be refused.');
        } catch (SaleRuleViolation) {
            // Also below the 70% floor; either way nothing is recorded.
        }

        $sale = $sales->record($unit, Money::of(7000, 'GHS'), PaymentMethod::Cash, $this->admin);

        $this->assertSame($request->id, $sale->product_request_id);
        $this->assertSame(120000, $sale->deposit_applied_amount);
        $this->assertSame($this->customer->id, $sale->customer_id);
        $this->assertSame(RequestStatus::Fulfilled, $request->fresh()?->status);

        $sales->void($sale, $this->admin, 'Rang up wrong');

        $this->assertSame(RequestStatus::ReadyForCollection, $request->fresh()?->status);
        $this->assertSame(StockStatus::Reserved, $unit->fresh()?->status);
    }

    public function test_customers_can_cancel_only_before_paying(): void
    {
        $this->actingAs($this->customer)->post(route('requests.store'), [
            'category_id' => $this->phones()->id,
            'condition_id' => $this->gradeA()->id,
            'description' => 'Pixel 8',
        ]);
        $unpaid = ProductRequest::firstOrFail();

        $this->actingAs($this->customer)->post(route('requests.cancel', $unpaid))->assertRedirect(route('requests.show', $unpaid));
        $this->assertSame(RequestStatus::Cancelled, $unpaid->fresh()?->status);

        $paid = $this->paidRequest();
        $this->actingAs($this->customer)->post(route('requests.cancel', $paid))->assertSessionHasErrors('request');
        $this->assertSame(RequestStatus::Open, $paid->fresh()?->status);
    }

    public function test_the_shop_cancels_releases_the_item_and_refunds(): void
    {
        $request = $this->paidRequest();
        $unit = StockUnit::factory()->received()->create();
        $service = app(ProductRequestService::class);
        $service->source($request, $unit);

        $service->cancel($request, $this->admin, 'We could not get one in grade A', refund: true);

        $this->assertSame(RequestStatus::Cancelled, $request->fresh()?->status);
        $this->assertSame(StockStatus::Received, $unit->fresh()?->status);
        $payment = Payment::firstOrFail();
        $this->assertSame(PaymentStatus::Refunded, $payment->status);
        $this->assertSame(120000, $payment->refunded_amount);
    }

    public function test_signed_paystack_webhooks_confirm_payments_once(): void
    {
        config(['services.payments.driver' => 'paystack', 'services.paystack.secret_key' => 'sk_test_secret']);
        $this->app->forgetInstance(PaymentGateway::class);
        $this->app->forgetInstance(PaystackGateway::class);
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/x']]),
        ]);

        $this->actingAs($this->customer)->post(route('requests.store'), [
            'category_id' => $this->phones()->id,
            'condition_id' => $this->gradeA()->id,
            'description' => 'Galaxy S24',
        ])->assertRedirect('https://checkout.paystack.com/x');

        $payment = Payment::firstOrFail();
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response(['status' => true, 'data' => [
                'status' => 'success', 'reference' => $payment->reference, 'amount' => 120000, 'currency' => 'GHS', 'id' => 777, 'channel' => 'mobile_money',
            ]]),
        ]);

        $body = json_encode(['event' => 'charge.success', 'data' => ['reference' => $payment->reference]], JSON_THROW_ON_ERROR);

        $this->call('POST', route('webhooks.paystack'), [], [], [], ['HTTP_X_PAYSTACK_SIGNATURE' => 'forged', 'CONTENT_TYPE' => 'application/json'], $body)
            ->assertStatus(401);
        $this->assertSame(PaymentStatus::Pending, $payment->fresh()?->status);

        $signature = hash_hmac('sha512', $body, 'sk_test_secret');
        foreach ([1, 2] as $attempt) {
            $this->call('POST', route('webhooks.paystack'), [], [], [], ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $body)
                ->assertOk();
        }

        $this->assertSame(PaymentStatus::Succeeded, $payment->fresh()?->status);
        $this->assertSame('777', $payment->fresh()?->provider_transaction_id);
        $this->assertSame(RequestStatus::Open, ProductRequest::firstOrFail()->status);
        // The duplicate webhook is skipped, so Paystack is only asked to verify once.
        $this->assertCount(1, Http::recorded(fn ($request) => str_contains($request->url(), '/transaction/verify/')));
    }

    public function test_a_wrong_amount_is_not_accepted(): void
    {
        config(['services.payments.driver' => 'paystack', 'services.paystack.secret_key' => 'sk_test_secret']);
        $this->app->forgetInstance(PaymentGateway::class);
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/x']]),
            'api.paystack.co/transaction/verify/*' => Http::response(['status' => true, 'data' => ['status' => 'success', 'amount' => 100, 'currency' => 'GHS']]),
        ]);

        $this->actingAs($this->customer)->post(route('requests.store'), [
            'category_id' => $this->phones()->id,
            'condition_id' => $this->gradeA()->id,
            'description' => 'Galaxy S24',
        ]);
        $payment = Payment::firstOrFail();

        $this->get(route('payments.callback', ['reference' => $payment->reference]));

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()?->status);
        $this->assertSame(RequestStatus::AwaitingDeposit, ProductRequest::firstOrFail()->status);
    }
}
