<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Models\User;
use App\Services\ShopSettings as Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ShopSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Shop settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return (bool) $user?->can(Permission::ManageSettings->value);
    }

    public function mount(Settings $settings): void
    {
        $this->form->fill([
            Settings::NEGOTIATION_FLOOR => $settings->negotiationFloorPercent(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('In-store sales')
                    ->schema([
                        TextInput::make(Settings::NEGOTIATION_FLOOR)
                            ->label('Lowest price staff can accept')
                            ->helperText('As a percentage of the listed price in cedis. Staff can\'t record a sale below it. It was 70% in 2020.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Save settings')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(Settings $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();

        $settings->set(Settings::NEGOTIATION_FLOOR, (int) $data[Settings::NEGOTIATION_FLOOR]);

        Notification::make()->success()->title('Settings saved')->send();
    }
}
