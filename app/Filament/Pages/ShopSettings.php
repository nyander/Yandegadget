<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Models\SettingChange;
use App\Models\User;
use App\Services\ShopSettings as Settings;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * The rules the app follows for haggling, deposits and collecting reserved
 * items. Each field shows what it means in cedis or dates as you type, and
 * every change is kept with who made it.
 *
 * @property-read Schema $form
 */
class ShopSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Shop settings';

    /** Labels and units for the change history. */
    public const LABELS = [
        Settings::NEGOTIATION_FLOOR => ['Lowest price staff can accept', '%'],
        Settings::CANCELLATION_FEE => ['Cancellation fee', '%'],
        Settings::COLLECTION_DAYS => ['Days to collect', ' days'],
        Settings::COLLECTION_REMINDER_DAYS => ['Reminder before the deadline', ' days'],
        Settings::RESERVATION_DEPOSIT => ['Online deposit', '%'],
        Settings::RESERVATION_DAYS => ['Days to collect an online reservation', ' days'],
    ];

    /** Example amounts for the live explanations, in cedis. */
    private const EXAMPLE_PRICE = 1000;

    private const EXAMPLE_DEPOSIT = 500;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return (bool) $user?->can(Permission::ManageSettings->value);
    }

    public function getSubheading(): string
    {
        return 'The rules the app follows when staff sell and when customers request items. Changes apply straight away, and each one is kept in the history at the bottom.';
    }

    public function mount(Settings $settings): void
    {
        $this->form->fill([
            Settings::NEGOTIATION_FLOOR => $settings->negotiationFloorPercent(),
            Settings::CANCELLATION_FEE => $settings->cancellationFeePercent(),
            Settings::COLLECTION_DAYS => $settings->collectionDays(),
            Settings::COLLECTION_REMINDER_DAYS => $settings->collectionReminderDays(),
            Settings::RESERVATION_DEPOSIT => $settings->reservationDepositPercent(),
            Settings::RESERVATION_DAYS => $settings->reservationDays(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Haggling in the store')
                    ->description('How far below the listed price staff can go. The Sell screen won\'t accept a lower price, so you stay in control of discounts.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->aside()
                    ->schema([
                        TextInput::make(Settings::NEGOTIATION_FLOOR)
                            ->label('Lowest price staff can accept')
                            ->belowContent(fn (Get $get) => $this->help('As a share of the listed price in cedis. It was 70% in 2020.', $this->floorExample($get(Settings::NEGOTIATION_FLOOR))))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(debounce: 400)
                            ->required()
                            ->extraFieldWrapperAttributes(['class' => 'yg-setting']),
                    ]),
                Section::make('Reserving online')
                    ->description('Customers can reserve an item that\'s in the store and pay by mobile money or card: the full listed price, or a deposit with the rest in store. If they don\'t collect it in time, they get everything back and it goes back on sale.')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->aside()
                    ->schema([
                        TextInput::make(Settings::RESERVATION_DEPOSIT)
                            ->label('Deposit when paying part online')
                            ->belowContent(fn (Get $get) => $this->help('A share of the listed price, rounded up to whole cedis. Customers can always choose to pay in full instead.', $this->reservationDepositExample($get(Settings::RESERVATION_DEPOSIT))))
                            ->numeric()
                            ->integer()
                            ->minValue(5)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(debounce: 400)
                            ->required()
                            ->extraFieldWrapperAttributes(['class' => 'yg-setting']),
                        TextInput::make(Settings::RESERVATION_DAYS)
                            ->label('Days to collect')
                            ->belowContent(fn (Get $get) => $this->help('Counted from when they pay. After this, they get a full refund and the item goes back on sale. Checked every few minutes.', $this->collectExample($get(Settings::RESERVATION_DAYS), 'paid for today')))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(14)
                            ->suffix('days')
                            ->live(debounce: 400)
                            ->required()
                            ->extraFieldWrapperAttributes(['class' => 'yg-setting']),
                    ]),
                Section::make('Customer requests')
                    ->description(new HtmlString('What happens to a deposit when a request is cancelled, and how long a found item waits in the store. The deposit for each grade is set under <a href="'.e(url('/admin/conditions')).'" class="yg-table__link">Catalogue → Conditions</a>.'))
                    ->icon(Heroicon::OutlinedMagnifyingGlassCircle)
                    ->aside()
                    ->schema([
                        TextInput::make(Settings::CANCELLATION_FEE)
                            ->label('Cancellation fee once the item is found')
                            ->belowContent(fn (Get $get) => $this->help('Kept if the customer cancels after you\'ve found their item, or doesn\'t collect it in time. If the shop cancels, or the customer cancels before you\'ve found it, the whole deposit is refunded.', $this->feeExample($get(Settings::CANCELLATION_FEE))))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(debounce: 400)
                            ->required()
                            ->extraFieldWrapperAttributes(['class' => 'yg-setting']),
                        TextInput::make(Settings::COLLECTION_DAYS)
                            ->label('Days to collect a reserved item')
                            ->belowContent(fn (Get $get) => $this->help('After this, the item goes back on sale and the cancellation fee is kept. Checked every morning.', $this->collectExample($get(Settings::COLLECTION_DAYS))))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(90)
                            ->suffix('days')
                            ->live(debounce: 400)
                            ->required()
                            ->extraFieldWrapperAttributes(['class' => 'yg-setting']),
                        TextInput::make(Settings::COLLECTION_REMINDER_DAYS)
                            ->label('Remind the customer before the deadline')
                            ->belowContent(fn (Get $get) => $this->help('An email (and a note on their account) this many days before their item goes back on sale. 0 sends no reminder.', $this->reminderExample($get(Settings::COLLECTION_DAYS), $get(Settings::COLLECTION_REMINDER_DAYS))))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(30)
                            ->lt(Settings::COLLECTION_DAYS)
                            ->validationMessages(['lt' => 'The reminder has to come before the deadline, so use fewer days than the days to collect.'])
                            ->suffix('days before')
                            ->live(debounce: 400)
                            ->required()
                            ->extraFieldWrapperAttributes(['class' => 'yg-setting']),
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
                        Action::make('reset')
                            ->label('Undo changes')
                            ->color('gray')
                            ->action(fn (Settings $settings) => $this->mount($settings)),
                    ]),
                ]),
            Section::make('Change history')
                ->description('Who changed a setting, when, and what it was before. Useful if a price or refund ever looks wrong.')
                ->icon(Heroicon::OutlinedClock)
                ->aside()
                ->schema([
                    View::make('filament.pages.setting-changes')
                        ->viewData(fn () => ['changes' => $this->changes()]),
                ]),
        ]);
    }

    public function save(Settings $settings): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();
        /** @var User $user */
        $user = auth()->user();

        foreach (array_keys(self::LABELS) as $key) {
            $settings->set($key, (int) $data[$key], $user);
        }

        Notification::make()->success()->title('Settings saved')->send();
    }

    /**
     * @return list<array{when: CarbonImmutable, who: string, what: string, from: string, to: string}>
     */
    private function changes(): array
    {
        return array_values(SettingChange::query()
            ->with('user')
            ->latest('created_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function (SettingChange $change) {
                [$label, $unit] = self::LABELS[$change->key] ?? [$change->key, ''];

                return [
                    'when' => $change->created_at,
                    'who' => $change->user->name ?? 'Someone',
                    'what' => $label,
                    'from' => $change->old_value === null ? '—' : $change->old_value.$unit,
                    'to' => $change->new_value.$unit,
                ];
            })
            ->all());
    }

    /** The explanation under a field, then its live example. */
    private function help(string $text, ?HtmlString $example): HtmlString
    {
        return new HtmlString('<p class="yg-setting__help">'.e($text).'</p>'.($example?->toHtml() ?? ''));
    }

    private function example(string $text, bool $warn = false): HtmlString
    {
        return new HtmlString('<p class="yg-setting__example'.($warn ? ' is-warn' : '').'">'.$text.'</p>');
    }

    private function cedis(float $amount): string
    {
        return 'GH₵'.number_format($amount, $amount == floor($amount) ? 0 : 2);
    }

    private function floorExample(mixed $percent): ?HtmlString
    {
        if (! is_numeric($percent) || $percent < 1 || $percent > 100) {
            return null;
        }

        $lowest = ceil(self::EXAMPLE_PRICE * $percent) / 100;
        $text = 'For example, an item listed at <strong>'.$this->cedis(self::EXAMPLE_PRICE).'</strong> can\'t be sold for less than <strong>'.$this->cedis($lowest).'</strong>.';

        return match (true) {
            $percent < 60 => $this->example($text.' That lets staff give away more than 40%. Check that\'s what you want.', true),
            $percent == 100 => $this->example($text.' Staff can\'t give any discount at all.', true),
            default => $this->example($text),
        };
    }

    private function feeExample(mixed $percent): ?HtmlString
    {
        if (! is_numeric($percent) || $percent < 0 || $percent > 100) {
            return null;
        }

        $kept = self::EXAMPLE_DEPOSIT * $percent / 100;
        $text = $percent == 0
            ? 'Customers always get their whole deposit back.'
            : 'On a <strong>'.$this->cedis(self::EXAMPLE_DEPOSIT).'</strong> deposit, you keep <strong>'.$this->cedis($kept).'</strong> and refund <strong>'.$this->cedis(self::EXAMPLE_DEPOSIT - $kept).'</strong>.';

        return $percent > 50
            ? $this->example($text.' Keeping more than half may put customers off paying deposits.', true)
            : $this->example($text);
    }

    private function collectExample(mixed $days, string $when = 'that\'s ready today'): ?HtmlString
    {
        if (! is_numeric($days) || $days < 1 || $days > 90) {
            return null;
        }

        $by = CarbonImmutable::today()->addDays((int) $days);

        return $this->example('An item '.e($when).' must be collected by <strong>'.$by->format('l j F').'</strong>.');
    }

    private function reservationDepositExample(mixed $percent): ?HtmlString
    {
        if (! is_numeric($percent) || $percent < 1 || $percent > 100) {
            return null;
        }

        $deposit = ceil(self::EXAMPLE_PRICE * $percent / 100);

        return $this->example('On a <strong>'.$this->cedis(self::EXAMPLE_PRICE).'</strong> item, they pay <strong>'.$this->cedis($deposit).'</strong> online and <strong>'.$this->cedis(self::EXAMPLE_PRICE - $deposit).'</strong> in store.');
    }

    private function reminderExample(mixed $days, mixed $before): ?HtmlString
    {
        if (! is_numeric($days) || ! is_numeric($before) || $before < 0) {
            return null;
        }

        if ((int) $before === 0) {
            return $this->example('No reminders: customers only hear from you when the item is ready.', true);
        }

        if ($before >= $days) {
            return null;
        }

        $by = CarbonImmutable::today()->addDays((int) $days);

        return $this->example('For that item, the reminder goes out on <strong>'.$by->subDays((int) $before)->format('l j F').'</strong>.');
    }
}
