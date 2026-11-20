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
            Settings::CANCELLATION_FEE => $settings->cancellationFeePercent(),
            Settings::COLLECTION_DAYS => $settings->collectionDays(),
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
                Section::make('Customer requests and deposits')
                    ->description('Deposit amounts are set per condition grade under Catalogue → Conditions. The shop cancelling, or the customer cancelling before we find the item, always refunds the whole deposit.')
                    ->schema([
                        TextInput::make(Settings::CANCELLATION_FEE)
                            ->label('Cancellation fee once the item is found')
                            ->helperText('Share of the deposit we keep if the customer cancels after we\'ve found their item, or doesn\'t collect it in time. The rest is refunded. 0% refunds everything.')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                        TextInput::make(Settings::COLLECTION_DAYS)
                            ->label('Days to collect a reserved item')
                            ->helperText('After this, the item goes back on sale and the cancellation fee applies. Checked every morning.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(90)
                            ->suffix('days')
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
        $settings->set(Settings::CANCELLATION_FEE, (int) $data[Settings::CANCELLATION_FEE]);
        $settings->set(Settings::COLLECTION_DAYS, (int) $data[Settings::COLLECTION_DAYS]);

        Notification::make()->success()->title('Settings saved')->send();
    }
}
