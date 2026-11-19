<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Enums\TransactionCategory;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Location;
use App\Models\TransactionType;
use App\Models\User;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_legon_store_and_uk_buying_base_are_seeded(): void
    {
        $legon = Location::where('code', 'ACC-LEGON')->firstOrFail();
        $uk = Location::where('code', 'UK-BUYING')->firstOrFail();

        $this->assertSame(LocationType::Store, $legon->type);
        $this->assertSame('GHS', $legon->currency);
        $this->assertSame(LocationType::Buying, $uk->type);
        $this->assertSame('GBP', $uk->currency);
    }

    public function test_staff_can_belong_to_a_location(): void
    {
        $legon = Location::where('code', 'ACC-LEGON')->firstOrFail();
        $user = User::factory()->create(['location_id' => $legon->id]);

        $this->assertTrue($user->location->is($legon));
    }

    public function test_the_seven_2020_categories_are_seeded_in_order(): void
    {
        $this->assertSame(
            ['Phones', 'Appliances', 'TV & Audio', 'Computing', 'Gaming', 'Cameras', 'Smart Tech'],
            Category::orderBy('sort_order')->pluck('name')->all(),
        );
    }

    public function test_condition_deposits_are_stored_as_money_in_minor_units(): void
    {
        $gradeA = Condition::where('grade', 'A')->firstOrFail();

        $this->assertTrue($gradeA->deposit->isEqualTo(Money::of(80, 'GBP')));
        $this->assertSame(8000, $gradeA->deposit_amount);
        $this->assertSame('GBP', $gradeA->deposit_currency);
    }

    public function test_money_cast_round_trips_other_currencies(): void
    {
        $condition = Condition::create([
            'grade' => 'D',
            'name' => 'Spares',
            'deposit' => Money::of('125.50', 'GHS'),
        ]);

        $fresh = $condition->fresh();

        $this->assertNotNull($fresh);
        $this->assertSame(12550, $fresh->deposit_amount);
        $this->assertTrue($fresh->deposit->isEqualTo(Money::of('125.50', 'GHS')));
    }

    public function test_money_cast_rejects_plain_numbers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Condition(['deposit' => 80]);
    }

    public function test_every_transaction_type_has_a_category(): void
    {
        $this->assertSame(27, TransactionType::count());

        $this->assertSame(
            TransactionCategory::CurrentLiability,
            TransactionType::where('name', 'Other Current Liabilities')->firstOrFail()->category,
        );
        $this->assertTrue(TransactionType::where('name', 'Inventory')->firstOrFail()->derived_in_reports);

        foreach (TransactionCategory::cases() as $category) {
            $this->assertGreaterThan(0, TransactionType::where('category', $category)->count(), $category->value);
        }
    }
}
