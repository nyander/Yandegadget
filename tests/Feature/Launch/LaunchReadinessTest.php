<?php

namespace Tests\Feature\Launch;

use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\ExchangeRate;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\AccountCloser;
use App\Services\SaleService;
use Brick\Money\Money;
use DomainException;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class LaunchReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_carry_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
    }

    public function test_missing_pages_show_the_friendly_error_page_outside_debug_mode(): void
    {
        config(['app.debug' => false]);

        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error')
                ->where('status', 404)
                ->has('auth'));

        $this->get('/shop/not-a-product')->assertNotFound()->assertInertia(fn (Assert $page) => $page->component('error'));
    }

    public function test_the_admin_panel_keeps_its_own_error_pages(): void
    {
        config(['app.debug' => false]);

        $this->actingAs(User::factory()->create()->assignRole(Role::Staff->value))
            ->get('/admin')
            ->assertForbidden()
            ->assertDontSee('data-page', false);
    }

    public function test_privacy_and_terms_pages_show_the_contact_details(): void
    {
        config(['shop.contact.email' => 'hello@yande.test', 'shop.dpc_registration' => null]);

        $this->get('/privacy')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('legal/privacy')
            ->where('contact.email', 'hello@yande.test')
            ->where('dpcRegistration', null));

        $this->get('/terms')->assertOk()->assertInertia(fn (Assert $page) => $page->component('legal/terms'));
    }

    public function test_the_sitemap_lists_only_products_customers_can_see(): void
    {
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15', 'effective_at' => now()->subDay()]);
        $visible = StockUnit::factory()->received()->create()->product;
        $hidden = StockUnit::factory()->status(StockStatus::InTransit)->create()->product;

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('shop.show', $visible), false)
            ->assertDontSee(route('shop.show', $hidden), false)
            ->assertSee(route('legal.privacy'), false);
    }

    public function test_robots_keeps_crawlers_out_of_private_areas_and_points_to_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /staff')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_create_admin_makes_a_verified_admin_and_emails_a_password_link(): void
    {
        Notification::fake();

        $this->artisan('shop:create-admin', ['email' => 'Owner@Yande.test', 'name' => 'Shop Owner'])
            ->expectsOutputToContain('owner@yande.test is an admin')
            ->assertSuccessful();

        $user = User::where('email', 'owner@yande.test')->firstOrFail();
        $this->assertTrue($user->hasRole(Role::Admin->value));
        $this->assertTrue($user->hasVerifiedEmail());
        Notification::assertSentTo($user, ResetPassword::class);

        // Running it again promotes rather than duplicates.
        $this->artisan('shop:create-admin', ['email' => 'owner@yande.test'])->assertSuccessful();
        $this->assertSame(1, User::where('email', 'owner@yande.test')->count());

        $this->artisan('shop:create-admin', ['email' => 'not-an-email'])->assertFailed();
    }

    public function test_closing_an_account_removes_personal_details_but_keeps_sales(): void
    {
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15', 'effective_at' => now()->subDay()]);
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $customer = User::factory()->create(['name' => 'Ama Mensah'])->assignRole(Role::Customer->value);

        $sale = app(SaleService::class)->record(
            StockUnit::factory()->received()->priced(100)->create(),
            Money::of(1500, 'GHS'),
            PaymentMethod::Cash,
            $admin,
            ['customer_id' => $customer->id, 'customer_name' => 'Ama Mensah', 'customer_phone' => '0240000000'],
        );

        $closer = app(AccountCloser::class);
        $closer->close($customer, $admin);

        $customer->refresh();
        $this->assertSame("Closed account #{$customer->id}", $customer->name);
        $this->assertTrue($closer->isClosed($customer));
        $this->assertFalse($customer->hasRole(Role::Customer->value));
        $this->assertNull($customer->email_verified_at);

        $sale->refresh();
        $this->assertNull($sale->customer_name);
        $this->assertNull($sale->customer_phone);
        $this->assertSame(150000, $sale->price_paid_amount);

        $this->expectException(DomainException::class);
        $closer->close($admin, $admin);
    }

    public function test_closed_accounts_are_hidden_from_the_users_list_unless_asked_for(): void
    {
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $staying = User::factory()->create(['name' => 'Kofi Boateng'])->assignRole(Role::Customer->value);
        $leaving = User::factory()->create()->assignRole(Role::Customer->value);
        app(AccountCloser::class)->close($leaving, $admin);
        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$staying, $admin])
            ->assertCanNotSeeTableRecords([$leaving])
            ->filterTable('closed', true)
            ->assertCanSeeTableRecords([$leaving])
            ->assertCanNotSeeTableRecords([$staying])
            ->assertSee('Closed: personal details removed');
    }
}
