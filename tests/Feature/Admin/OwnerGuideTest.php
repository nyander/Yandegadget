<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Pages\Help;
use App\Filament\Widgets\GettingStartedChecklist;
use App\Models\ExchangeRate;
use App\Models\User;
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
}
