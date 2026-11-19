<?php

namespace Tests\Feature\Admin;

use App\Enums\OfferStatus;
use App\Enums\Role;
use App\Filament\Resources\SupplierOffers\Pages\ListSupplierOffers;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SupplierOfferService;
use Brick\Money\Money;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

class SupplierAndUserAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('admin');
    }

    public function test_admin_buys_a_supplier_offer(): void
    {
        $supplierUser = User::factory()->create()->assignRole(Role::Supplier->value);
        $supplier = Supplier::factory()->create(['user_id' => $supplierUser->id]);
        $offer = app(SupplierOfferService::class)->submit($supplier, $supplierUser, [
            'category_id' => (int) Category::where('slug', 'gaming')->value('id'),
            'condition_id' => (int) Condition::where('grade', 'B')->value('id'),
            'title' => 'Steam Deck OLED',
            'asking_price' => Money::of(300, 'GBP'),
        ]);

        $this->get('/admin/supplier-offers')->assertOk()->assertSee($offer->reference);

        Livewire::test(ListSupplierOffers::class)
            ->callAction(TestAction::make('accept')->table($offer), [
                'agreed_price' => '280',
                'list_price' => '380',
                'location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            ])
            ->assertHasNoFormErrors();

        $offer->refresh();
        $this->assertSame(OfferStatus::Accepted, $offer->status);
        $this->assertSame(28000, $offer->agreed_price_amount);
        $this->assertSame(38000, $offer->stockUnit?->price_amount);
    }

    public function test_admin_adds_a_staff_member_who_gets_an_invitation(): void
    {
        Notification::fake();
        $this->get('/admin/users')->assertOk();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Abena Owusu',
                'email' => 'abena@example.test',
                'roles' => [RoleModel::findByName(Role::Staff->value)->id],
                'location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $staff = User::where('email', 'abena@example.test')->firstOrFail();
        $this->assertTrue($staff->hasRole(Role::Staff->value));
        $this->assertNotNull($staff->email_verified_at);
        Notification::assertSentTo($staff, ResetPassword::class);
    }

    public function test_admins_cannot_remove_their_own_admin_role(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->fillForm(['roles' => [RoleModel::findByName(Role::Customer->value)->id]])
            ->call('save')
            ->assertHasFormErrors(['roles']);

        $this->assertTrue($this->admin->fresh()?->hasRole(Role::Admin->value));
    }
}
