<?php

namespace Tests\Feature\Suppliers;

use App\Enums\OfferStatus;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Exceptions\OfferRuleViolation;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockUnit;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Notifications\SupplierOfferReviewed;
use App\Services\SupplierOfferService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupplierOfferServiceTest extends TestCase
{
    use RefreshDatabase;

    private SupplierOfferService $offers;

    private User $admin;

    private User $supplierUser;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->offers = app(SupplierOfferService::class);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->supplierUser = User::factory()->create()->assignRole(Role::Supplier->value);
        $this->supplier = Supplier::factory()->create(['user_id' => $this->supplierUser->id]);
    }

    private function offer(): SupplierOffer
    {
        return $this->offers->submit($this->supplier, $this->supplierUser, [
            'category_id' => (int) Category::where('slug', 'phones')->value('id'),
            'condition_id' => (int) Condition::where('grade', 'A')->value('id'),
            'title' => 'iPhone 15 128GB',
            'brand' => 'Apple',
            'serial_number' => 'IMEI123',
            'description' => 'Boxed, like new',
            'asking_price' => Money::of(450, 'GBP'),
        ], [UploadedFile::fake()->image('front.jpg')]);
    }

    private function terms(array $overrides = []): array
    {
        return [
            'agreed_price' => Money::of(430, 'GBP'),
            'list_price' => Money::of(560, 'GBP'),
            'location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            ...$overrides,
        ];
    }

    public function test_buying_an_offer_creates_a_product_and_a_stock_unit_ready_to_ship(): void
    {
        Notification::fake();
        $offer = $this->offer();

        $unit = $this->offers->accept($offer, $this->admin, $this->terms());

        $this->assertSame(StockStatus::Purchased, $unit->status);
        $this->assertTrue($unit->cost->isEqualTo(Money::of(430, 'GBP')));
        $this->assertTrue($unit->price->isEqualTo(Money::of(560, 'GBP')));
        $this->assertSame($this->supplier->id, $unit->supplier_id);
        $this->assertSame('IMEI123', $unit->serial_number);
        $this->assertSame('Boxed, like new', $unit->condition_notes);
        $this->assertSame('iPhone 15 128GB', $unit->product->name);
        $this->assertCount(1, $unit->getMedia('photos'));
        $this->assertTrue(StockUnit::readyToShip()->whereKey($unit->id)->exists());

        $offer->refresh();
        $this->assertSame(OfferStatus::Accepted, $offer->status);
        $this->assertSame(43000, $offer->agreed_price_amount);
        $this->assertSame($unit->id, $offer->stock_unit_id);
        Notification::assertSentTo($this->supplierUser, SupplierOfferReviewed::class);
    }

    public function test_buying_into_an_existing_product(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 15 128GB']);

        $unit = $this->offers->accept($this->offer(), $this->admin, $this->terms(['product_id' => $product->id]));

        $this->assertSame($product->id, $unit->product_id);
        $this->assertSame(1, Product::count());
    }

    public function test_an_offer_can_only_be_reviewed_once(): void
    {
        $offer = $this->offer();
        $this->offers->accept($offer, $this->admin, $this->terms());

        $this->expectException(OfferRuleViolation::class);
        $this->offers->decline($offer->fresh() ?? $offer, $this->admin, 'Too late');
    }

    public function test_declining_tells_the_supplier_why(): void
    {
        Notification::fake();
        $offer = $this->offer();

        $this->offers->decline($offer, $this->admin, 'We have plenty of these');

        $this->assertSame(OfferStatus::Declined, $offer->fresh()?->status);
        Notification::assertSentTo($this->supplierUser, SupplierOfferReviewed::class,
            fn (SupplierOfferReviewed $n) => str_contains($n->toDatabase($this->supplierUser)['body'], 'We have plenty of these'));
    }

    public function test_suppliers_cannot_offer_for_another_business(): void
    {
        $other = User::factory()->create()->assignRole(Role::Supplier->value);

        $this->expectException(OfferRuleViolation::class);
        $this->offers->submit($this->supplier, $other, [
            'category_id' => 1, 'condition_id' => 1, 'title' => 'x', 'asking_price' => Money::of(1, 'GBP'),
        ]);
    }
}
