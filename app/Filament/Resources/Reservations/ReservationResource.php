<?php

namespace App\Filament\Resources\Reservations;

use App\Enums\ReservationStatus;
use App\Exceptions\ReservationRuleViolation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Pages\ViewReservation;
use App\Filament\Resources\StockUnits\StockUnitResource;
use App\Models\Reservation;
use App\Services\Payments\GatewayException;
use App\Services\ReservationService;
use BackedEnum;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Items customers have reserved and paid for online (in full or a deposit),
 * waiting to be collected and sold to them in store.
 */
class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Online reservations';

    protected static ?string $modelLabel = 'reservation';

    protected static ?string $pluralModelLabel = 'online reservations';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationBadge(): ?string
    {
        $waiting = Reservation::where('status', ReservationStatus::Reserved)->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function table(Table $table): Table
    {
        $money = fn (Money $state) => $state->formatToLocale((string) config('shop.display_locale'));

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'stockUnit.product', 'stockUnit.condition']))
            ->recordUrl(fn (Reservation $record) => self::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('reference')->weight('bold')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('stockUnit.product.name')->label('Item')->searchable()->wrap()
                    ->description(fn (Reservation $record) => "{$record->stockUnit->reference} · Grade {$record->stockUnit->condition->grade}"),
                TextColumn::make('customer.name')->label('Customer')->searchable()
                    ->description(fn (Reservation $record) => $record->customer->email),
                TextColumn::make('due')->label('Paid online')->formatStateUsing($money)
                    ->description(fn (Reservation $record) => $record->payment_option->getLabel()),
                TextColumn::make('balance')->label('To pay in store')
                    ->state(fn (Reservation $record) => $record->balance())
                    ->formatStateUsing($money),
                TextColumn::make('collect_by')->label('Collect by')->date('D j M')->placeholder('—')
                    ->color(fn (Reservation $record) => $record->collect_by?->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('status')->options(ReservationStatus::class)->multiple()
                    ->default([ReservationStatus::Reserved->value]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Open'),
                    self::sellAction(),
                    self::cancelAction(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $money = fn (?Money $state) => $state?->formatToLocale((string) config('shop.display_locale'));

        return $schema->columns(1)->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Section::make('Item')->icon(Heroicon::OutlinedCube)->schema([
                    TextEntry::make('stockUnit.product.name')->label('Product')->weight('bold'),
                    TextEntry::make('stockUnit.reference')->label('Stock unit')->color('primary')
                        ->url(fn (Reservation $record) => StockUnitResource::getUrl('edit', ['record' => $record->stockUnit])),
                    TextEntry::make('stockUnit.condition.name')->label('Condition'),
                ]),
                Section::make('Customer')->icon(Heroicon::OutlinedUser)->schema([
                    TextEntry::make('customer.name')->label('Name'),
                    TextEntry::make('customer.email')->label('Email')->copyable(),
                ]),
                Section::make('Money')->icon(Heroicon::OutlinedBanknotes)->schema([
                    TextEntry::make('price')->label('Listed price')->formatStateUsing($money),
                    TextEntry::make('due')->label('Paid online')->formatStateUsing($money)
                        ->helperText(fn (Reservation $record) => $record->payment_option->getLabel().($record->latestPayment?->channel ? ' · '.str($record->latestPayment->channel)->replace('_', ' ') : '')),
                    TextEntry::make('balance')->label('To pay in store')->state(fn (Reservation $record) => $record->balance())->formatStateUsing($money),
                ]),
            ]),
            Section::make('Status')->icon(Heroicon::OutlinedClock)->columns(['default' => 2, 'lg' => 4])->schema([
                TextEntry::make('status')->badge(),
                TextEntry::make('paid_at')->label('Paid')->dateTime('j M Y, H:i')->placeholder('Not yet'),
                TextEntry::make('collect_by')->label('Collect by')->dateTime('l j M')->placeholder('—'),
                TextEntry::make('collected_at')->label('Collected')->dateTime('j M Y, H:i')->placeholder('Not yet'),
                TextEntry::make('cancel_reason')->label('Why it was cancelled')->columnSpanFull()
                    ->visible(fn (Reservation $record) => $record->status === ReservationStatus::Cancelled),
            ]),
        ]);
    }

    /** Opens the staff sale screen for the item, where the online payment comes off the price. */
    public static function sellAction(): Action
    {
        return Action::make('sell')
            ->label('Sell to them')
            ->icon(Heroicon::OutlinedBanknotes)
            ->visible(fn (Reservation $record) => $record->status === ReservationStatus::Reserved)
            ->url(fn (Reservation $record) => route('staff.sell.create', $record->stockUnit));
    }

    public static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel and refund')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Reservation $record) => $record->status->holdsItem())
            ->modalDescription('The item goes back on sale and the customer gets everything they paid back, by mobile money or card. They\'re told by email.')
            ->schema([
                Textarea::make('reason')->label('Reason (shown to the customer)')->required()->maxLength(500),
            ])
            ->action(function (Reservation $record, array $data, ReservationService $reservations): void {
                try {
                    $reservations->cancel($record, 'shop', (string) $data['reason']);
                } catch (ReservationRuleViolation|GatewayException $e) {
                    Notification::make()->danger()->title('Not cancelled')->body($e->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title("{$record->reference} cancelled and refunded")->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'view' => ViewReservation::route('/{record}'),
        ];
    }
}
