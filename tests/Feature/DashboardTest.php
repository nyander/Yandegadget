<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_customers_see_their_latest_requests(): void
    {
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);

        foreach (['iPhone 14', 'Galaxy S23', 'PS5', 'MacBook Air'] as $item) {
            $this->actingAs($customer)->post(route('requests.store'), [
                'category_id' => Category::where('slug', 'phones')->firstOrFail()->id,
                'condition_id' => Condition::where('grade', 'A')->firstOrFail()->id,
                'description' => $item,
            ]);
        }

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('recentRequests', 3)
                ->where('activeRequests', 4)
                ->where('readyRequests', 0));
    }

    public function test_staff_get_no_request_list(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->has('recentRequests', 0));
    }
}
