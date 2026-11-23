<?php

namespace App\Filament\Pages;

use App\Support\HelpCentre;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * The owner's guide: short articles on every part of the back office.
 */
class Help extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|UnitEnum|null $navigationGroup = 'Help';

    protected static ?string $navigationLabel = 'Guide';

    protected static ?string $slug = 'help';

    protected string $view = 'filament.pages.help';

    #[Url]
    public ?string $topic = null;

    public function mount(HelpCentre $help): void
    {
        if (! $help->exists($this->topic)) {
            $this->topic = null;
        }
    }

    public function getTitle(): string|Htmlable
    {
        $help = app(HelpCentre::class);

        return $help->exists($this->topic) ? $help->topic((string) $this->topic)['title'] : 'Guide';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['help' => app(HelpCentre::class)];
    }
}
