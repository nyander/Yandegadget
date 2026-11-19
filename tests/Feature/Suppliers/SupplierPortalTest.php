<?php

namespace Tests\Feature\Suppliers;

use App\Enums\OfferStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Notifications\NewSupplierOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SupplierPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $supplierUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->supplierUser = User::factory()->create()->assignRole(Role::Supplier->value);
    }

    private function withProfile(?User $user = null): Supplier
    {
        $user ??= $this->supplierUser;

        return Supplier::factory()->create(['user_id' => $user->id]);
    }

    private function offerData(array $overrides = []): array
    {
        return [
            'title' => 'MacBook Air M2 256GB',
            'category_id' => Category::where('slug', 'computing')->value('id'),
            'condition_id' => Condition::where('grade', 'B')->value('id'),
            'brand' => 'Apple',
            'description' => 'Light scuff on the lid. Charger included.',
            'asking_price' => '420.00',
            'photos' => [UploadedFile::fake()->image('lid.jpg'), UploadedFile::fake()->image('screen.jpg')],
            ...$overrides,
        ];
    }

    public function test_suppliers_fill_in_their_business_first(): void
    {
        $this->actingAs($this->supplierUser)->get(route('supplier.offers.index'))
            ->assertRedirect(route('supplier.profile.edit'));

        $this->actingAs($this->supplierUser)->put(route('supplier.profile.update'), [
            'name' => 'London Tech Traders',
            'contact_name' => 'Sam',
            'email' => 'sam@example.test',
            'phone' => '07700 900000',
            'address' => '1 High Street, London',
        ])->assertRedirect(route('supplier.offers.index'));

        $this->assertSame('London Tech Traders', $this->supplierUser->fresh()?->supplier?->name);
        $this->actingAs($this->supplierUser)->get(route('supplier.offers.index'))->assertOk();
    }

    public function test_suppliers_offer_items_with_photos_and_the_shop_is_told(): void
    {
        Notification::fake();
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $supplier = $this->withProfile();

        $response = $this->actingAs($this->supplierUser)->post(route('supplier.offers.store'), $this->offerData());

        $offer = SupplierOffer::firstOrFail();
        $response->assertRedirect(route('supplier.offers.show', $offer));
        $this->assertSame(sprintf('OF-%05d', $offer->id), $offer->reference);
        $this->assertSame($supplier->id, $offer->supplier_id);
        $this->assertSame(42000, $offer->asking_price_amount);
        $this->assertSame('GBP', $offer->asking_price_currency);
        $this->assertCount(2, $offer->getMedia('photos'));
        Notification::assertSentTo($admin, NewSupplierOffer::class);

        $this->actingAs($this->supplierUser)->get(route('supplier.offers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('supplier/offers/index')
                ->has('offers', 1)
                ->where('stats.waiting', 1));
    }

    public function test_offers_need_photos_and_a_sensible_price(): void
    {
        $this->withProfile();

        $this->actingAs($this->supplierUser)
            ->post(route('supplier.offers.store'), $this->offerData(['photos' => [], 'asking_price' => '-5']))
            ->assertSessionHasErrors(['photos', 'asking_price']);
    }

    public function test_suppliers_only_see_their_own_offers(): void
    {
        $this->withProfile();
        $this->actingAs($this->supplierUser)->post(route('supplier.offers.store'), $this->offerData());
        $offer = SupplierOffer::firstOrFail();

        $rival = User::factory()->create()->assignRole(Role::Supplier->value);
        $this->withProfile($rival);

        $this->actingAs($rival)->get(route('supplier.offers.show', $offer))->assertForbidden();
        $this->actingAs($rival)->post(route('supplier.offers.withdraw', $offer))->assertForbidden();
        $this->actingAs($rival)->get(route('supplier.offers.index'))
            ->assertInertia(fn (Assert $page) => $page->has('offers', 0));
    }

    public function test_suppliers_can_withdraw_offers_waiting_for_review(): void
    {
        $this->withProfile();
        $this->actingAs($this->supplierUser)->post(route('supplier.offers.store'), $this->offerData());
        $offer = SupplierOffer::firstOrFail();

        $this->actingAs($this->supplierUser)->post(route('supplier.offers.withdraw', $offer))
            ->assertRedirect(route('supplier.offers.show', $offer));

        $this->assertSame(OfferStatus::Withdrawn, $offer->fresh()?->status);
    }

    public function test_customers_cannot_use_the_supplier_portal(): void
    {
        $customer = User::factory()->create()->assignRole(Role::Customer->value);

        $this->actingAs($customer)->get(route('supplier.offers.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('supplier.profile.edit'))->assertForbidden();
    }
}
