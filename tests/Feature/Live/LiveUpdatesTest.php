<?php

namespace Tests\Feature\Live;

use App\Enums\Role;
use App\Events\RecordsChanged;
use App\Models\BuyingListItem;
use App\Models\Category;
use App\Models\Condition;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\ProductRequestService;
use App\Support\LiveUpdates;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class LiveUpdatesTest extends TestCase
{
    use RefreshDatabase;

    private function turnOn(): void
    {
        config(['broadcasting.default' => 'reverb']);
        $this->app->forgetScopedInstances();
    }

    public function test_saving_a_record_tells_the_right_people_which_area_changed(): void
    {
        $this->turnOn();
        Event::fake([RecordsChanged::class]);

        BuyingListItem::create(['description' => 'Pixel 8', 'quantity' => 1]);

        Event::assertDispatched(RecordsChanged::class, fn (RecordsChanged $e) => $e->topic === 'buying-list'
            && $e->channels === [LiveUpdates::BACKOFFICE]
            && $e->broadcastWith() === ['topic' => 'buying-list']
            && $e->broadcastAs() === 'records.changed');

        // Stock goes to the back office and to store staff.
        $this->app->forgetScopedInstances();
        StockUnit::factory()->create();
        Event::assertDispatched(RecordsChanged::class, fn (RecordsChanged $e) => $e->topic === 'stock'
            && $e->channels === [LiveUpdates::BACKOFFICE, LiveUpdates::STORE]);
    }

    public function test_a_customer_hears_about_their_own_request(): void
    {
        $this->turnOn();
        Event::fake([RecordsChanged::class]);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $request = app(ProductRequestService::class)->create($customer, [
            'category_id' => Category::where('slug', 'phones')->value('id'),
            'condition_id' => Condition::orderBy('sort_order')->value('id'),
            'description' => 'iPhone 13',
        ]);

        Event::assertDispatched(RecordsChanged::class, fn (RecordsChanged $e) => $e->topic === 'requests'
            && in_array(LiveUpdates::userChannel($request->customer_id), $e->channels, true));
    }

    public function test_many_changes_in_one_go_send_one_signal_after_saving(): void
    {
        $this->turnOn();
        Event::fake([RecordsChanged::class]);

        DB::transaction(function () {
            BuyingListItem::create(['description' => 'One', 'quantity' => 1]);
            BuyingListItem::create(['description' => 'Two', 'quantity' => 1]);
            Event::assertNotDispatched(RecordsChanged::class);
        });

        Event::assertDispatchedTimes(RecordsChanged::class, 1);
    }

    public function test_nothing_is_sent_when_live_updates_are_off(): void
    {
        Event::fake([RecordsChanged::class]);

        BuyingListItem::create(['description' => 'Pixel 8', 'quantity' => 1]);

        Event::assertNotDispatched(RecordsChanged::class);
    }

    public function test_saving_still_works_when_reverb_is_down(): void
    {
        $this->turnOn();
        $broken = \Mockery::mock(BroadcastManager::class);
        $broken->shouldReceive('event')->andThrow(new RuntimeException('Failed to connect to localhost port 8080'));
        $this->app->instance(BroadcastManager::class, $broken);
        Broadcast::swap($broken);

        BuyingListItem::create(['description' => 'Pixel 8', 'quantity' => 1]);

        $this->assertSame(1, BuyingListItem::count());
    }

    public function test_only_the_right_people_can_listen(): void
    {
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $staff = User::factory()->create()->assignRole(Role::Staff->value);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);

        /** @var array<string, callable> $channels */
        $channels = Broadcast::driver()->getChannels()->all();
        $can = fn (User $user, string $channel, mixed ...$args) => (bool) $channels[$channel]($user, ...$args);

        $this->assertTrue($can($admin, LiveUpdates::BACKOFFICE));
        $this->assertFalse($can($staff, LiveUpdates::BACKOFFICE));
        $this->assertFalse($can($customer, LiveUpdates::BACKOFFICE));
        $this->assertTrue($can($staff, LiveUpdates::STORE));
        $this->assertFalse($can($customer, LiveUpdates::STORE));
        $this->assertTrue($can($customer, 'App.Models.User.{id}', $customer->id));
        $this->assertFalse($can($customer, 'App.Models.User.{id}', $staff->id));
    }
}
