<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Pages\Help;
use App\Filament\Widgets\GettingStartedChecklist;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Support\AdminTours;
use App\Support\GettingStarted;
use App\Support\HelpCentre;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerGuideTest extends TestCase
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

    public function test_the_guide_lists_every_topic_and_opens_each_article(): void
    {
        $help = app(HelpCentre::class);

        $index = $this->get('/admin/help')->assertOk()->assertSee('Start here');

        foreach ($help->topics() as $slug => $topic) {
            $index->assertSee($topic['title']);
            $this->get('/admin/help?topic='.$slug)->assertOk()->assertSee($topic['title']);
        }

        // An unknown topic falls back to the list.
        Livewire::test(Help::class, ['topic' => 'nope'])->assertSet('topic', null);
    }

    public function test_every_link_in_the_guide_goes_somewhere_real(): void
    {
        $help = app(HelpCentre::class);

        foreach (array_keys($help->topics()) as $slug) {
            $markdown = (string) file_get_contents(resource_path("help/{$slug}.md"));
            preg_match_all('/\]\((help|admin):([a-z0-9\-\/]*)\)/', $markdown, $links, PREG_SET_ORDER);

            foreach ($links as [, $kind, $target]) {
                if ($kind === 'help') {
                    $this->assertTrue($help->exists($target), "{$slug}.md links to a missing article [{$target}].");
                } else {
                    $this->get('/admin'.($target === '' ? '' : '/'.$target))->assertOk();
                }
            }

            $this->assertStringNotContainsString('help:', (string) $help->render($slug), "{$slug}.md has an unconverted link.");
        }
    }

    public function test_the_guide_is_only_for_people_who_can_use_the_back_office(): void
    {
        $this->actingAs(User::factory()->create()->assignRole(Role::Staff->value));

        $this->get('/admin/help')->assertForbidden();
    }

    public function test_the_checklist_ticks_steps_off_from_the_data_and_by_hand(): void
    {
        $checklist = app(GettingStarted::class);
        $step = fn (string $key) => collect($checklist->steps($this->admin))->firstWhere('key', $key);

        $this->assertFalse($step('rate')['done']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15', 'effective_at' => now()]);
        $this->assertTrue($step('rate')['done']);

        $this->assertTrue($step('deposits')['manual']);
        Livewire::test(GettingStartedChecklist::class)
            ->assertSee('Getting started')
            ->assertSee('Check the deposit for each condition')
            ->call('markDone', 'deposits');
        $this->assertTrue($step('deposits')['done']);

        Livewire::test(GettingStartedChecklist::class)->call('dismiss')->assertRedirect(url('/admin'));
        $this->assertFalse(GettingStartedChecklist::canView());
    }

    public function test_every_tour_belongs_to_a_real_admin_page(): void
    {
        $routes = app('router')->getRoutes();
        $tours = (new \ReflectionClass(AdminTours::class))->getMethod('tours')->invoke(app(AdminTours::class));

        foreach ($tours as $key => $steps) {
            $this->assertTrue($routes->hasNamedRoute("filament.admin.{$key}"), "No admin page for the [{$key}] tour.");
            $this->assertNotEmpty($steps);
        }
    }

    public function test_pages_with_a_tour_send_its_steps_and_offer_it_in_the_help_menu(): void
    {
        $this->get('/admin/product-requests')
            ->assertOk()
            ->assertSee('id="yg-tour-data"', false)
            ->assertSee('Open a request')
            ->assertSee('Show me around this page')
            ->assertSee('Tips on each page');

        // A page without a tour still has the guide and the toggle, but no tour.
        $this->get('/admin/carriers')
            ->assertOk()
            ->assertDontSee('id="yg-tour-data"', false)
            ->assertDontSee('Show me around this page')
            ->assertSee('Read the guide');
    }
}
