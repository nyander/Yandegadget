<?php

namespace Tests\Feature\Admin;

use App\Enums\CancellationCause;
use App\Enums\PaymentStatus;
use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Pages\ShopSettings as ShopSettingsPage;
use App\Filament\Resources\ProductRequests\Pages\ListProductRequests;
use App\Filament\Resources\ProductRequests\Pages\ViewProductRequest;
use App\Filament\Resources\ProductRequests\ProductRequestResource;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\SettingChange;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\ProductRequestService;
use App\Services\ShopSettings;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class RequestAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_matches_an_open_request_to_a_unit(): void
    {
        config(['services.payments.driver' => 'fake']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $service = app(ProductRequestService::class);
        $request = $service->create($customer, [
            'category_id' => Category::where('slug', 'phones')->value('id'),
            'condition_id' => Condition::where('grade', 'B')->value('id'),
            'description' => 'iPhone 13',
        ]);
        $service->depositPaid($request);
        $unit = StockUnit::factory()->received()->create();

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        $this->get('/admin/product-requests')->assertOk()->assertSee($request->reference);
        $this->get('/admin/payments')->assertOk();

        Livewire::test(ListProductRequests::class)
            ->callAction(TestAction::make('match')->table($request), ['stock_unit_id' => $unit->id])
            ->assertHasNoFormErrors();

        $this->assertSame(RequestStatus::ReadyForCollection, $request->fresh()?->status);
        $this->assertSame(StockStatus::Reserved, $unit->fresh()?->status);
        $this->assertFalse($admin->can('update', ProductRequest::first()));
    }

    public function test_admin_cancels_for_the_customer_and_the_fee_is_kept(): void
    {
        config(['services.payments.driver' => 'fake']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $service = app(ProductRequestService::class);
        $request = $service->create($customer, [
            'category_id' => Category::where('slug', 'phones')->value('id'),
            'condition_id' => Condition::where('grade', 'B')->value('id'),
            'description' => 'iPhone 13',
        ]);
        $payment = new Payment;
        $payment->forceFill([
            'payable_type' => $request->getMorphClass(),
            'payable_id' => $request->id,
            'customer_id' => $customer->id,
            'provider' => 'fake',
            'reference' => 'PAY-ADMIN-1',
            'status' => PaymentStatus::Succeeded,
            'amount' => 40000,
            'currency' => 'GHS',
            'paid_at' => now(),
        ])->save();
        $service->depositPaid($request);
        $service->source($request->refresh(), StockUnit::factory()->received()->create());

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        Livewire::test(ListProductRequests::class)
            ->callAction(TestAction::make('cancel')->table($request), [
                'cause' => CancellationCause::Customer->value,
                'reason' => 'Customer found one elsewhere',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(CancellationCause::Customer, $request->fresh()?->cancelled_by);
        $this->assertSame(30000, $payment->fresh()?->refunded_amount);
    }

    public function test_admin_sets_the_cancellation_fee_and_collection_window(): void
    {
        $this->actingAs(User::factory()->create()->assignRole(Role::Admin->value));
        Filament::setCurrentPanel('admin');

        Livewire::test(ShopSettingsPage::class)
            ->assertSchemaStateSet([ShopSettings::CANCELLATION_FEE => 25, ShopSettings::COLLECTION_DAYS => 14], 'form')
            ->fillForm([ShopSettings::NEGOTIATION_FLOOR => 70, ShopSettings::CANCELLATION_FEE => 20, ShopSettings::COLLECTION_DAYS => 10, ShopSettings::COLLECTION_REMINDER_DAYS => 2])
            ->assertSeeText('you keep GH₵100 and refund GH₵400')
            ->call('save')
            ->assertHasNoFormErrors()
            // Each change is kept, with who made it.
            ->assertSee(['Cancellation fee', '25%', '20%', 'Reminder before the deadline']);

        $this->assertSame(3, SettingChange::count());

        // The reminder has to come before the deadline.
        Livewire::test(ShopSettingsPage::class)
            ->fillForm([ShopSettings::COLLECTION_DAYS => 5, ShopSettings::COLLECTION_REMINDER_DAYS => 5])
            ->call('save')
            ->assertHasFormErrors([ShopSettings::COLLECTION_REMINDER_DAYS => 'lt']);

        $settings = app(ShopSettings::class);
        $this->assertSame(20, $settings->cancellationFeePercent());
        $this->assertSame(10, $settings->collectionDays());

        $this->get('/terms')->assertInertia(fn (Assert $page) => $page
            ->where('cancellationFeePercent', 20)
            ->where('collectionDays', 10));
    }

    public function test_admin_opens_a_request_and_declines_it_with_a_full_refund(): void
    {
        config(['services.payments.driver' => 'fake']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $service = app(ProductRequestService::class);
        $request = $service->create($customer, [
            'category_id' => Category::where('slug', 'phones')->value('id'),
            'condition_id' => Condition::where('grade', 'B')->value('id'),
            'description' => 'Pixel 8',
            'notes' => 'Black if possible',
        ]);
        $service->depositPaid($request);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        $this->get(ProductRequestResource::getUrl('view', ['record' => $request]))
            ->assertOk()
            ->assertSee(['Pixel 8', 'Black if possible', $customer->email, 'Looking for it', 'Match an item', 'Decline']);

        Livewire::test(ViewProductRequest::class, ['record' => $request->getRouteKey()])
            ->assertActionVisible('match')
            ->assertActionVisible('decline')
            ->callAction('decline', ['reason' => 'We could not find one'])
            ->assertHasNoFormErrors();

        $request->refresh();
        $this->assertSame(RequestStatus::Cancelled, $request->status);
        $this->assertSame(CancellationCause::Shop, $request->cancelled_by);
        $this->assertSame('We could not find one', $request->cancel_reason);

        Livewire::test(ViewProductRequest::class, ['record' => $request->getRouteKey()])
            ->assertActionHidden('match')
            ->assertActionHidden('decline')
            ->assertSee('Cancelled by the shop');
    }
}
