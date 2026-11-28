<?php

namespace App\Filament\Resources\SupplierOffers;

use App\Enums\LocationType;
use App\Enums\OfferStatus;
use App\Exceptions\OfferRuleViolation;
use App\Filament\Resources\SupplierOffers\Pages\ListSupplierOffers;
use App\Filament\Support\MoneyInput;
use App\Models\Location;
use App\Models\Product;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Services\SupplierOfferService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SupplierOfferResource extends Resource
{
    protected static ?string $model = SupplierOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Buying';

    protected static ?string $navigationLabel = 'Supplier offers';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationBadge(): ?string
    {
        $waiting = SupplierOffer::where('status', OfferStatus::Submitted)->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function table(Table $table): Table
    {
        $currency = (string) config('shop.list_currency');

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['supplier', 'condition', 'category', 'stockUnit', 'media']))
            ->columns([
                SpatieMediaLibraryImageColumn::make('photos')->collection('photos')->conversion('thumb')->stacked()->limit(3)->label(''),
                TextColumn::make('reference')->searchable()->weight('bold'),
                TextColumn::make('status')->badge(),
                TextColumn::make('title')->label('Item')->searchable()->wrap()
                    ->description(fn (SupplierOffer $record) => "{$record->category->name} · Grade {$record->condition->grade}"),
                TextColumn::make('supplier.name')->searchable(),
                TextColumn::make('asking_price')->label('Asking')
                    ->state(fn (SupplierOffer $record) => $record->asking_price->formatToLocale('en_GB')),
                TextColumn::make('agreed_price')->label('Agreed')
                    ->state(fn (SupplierOffer $record) => $record->agreed_price?->formatToLocale('en_GB'))
                    ->placeholder('—'),
                TextColumn::make('stockUnit.reference')->label('Stock unit')->placeholder('—'),
                TextColumn::make('description')->wrap()->limit(120)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Offered')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OfferStatus::class)->default(OfferStatus::Submitted->value),
                SelectFilter::make('supplier')->relationship('supplier', 'name'),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('Buy')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (SupplierOffer $record) => $record->status === OfferStatus::Submitted)
                    ->modalDescription('Creates a stock unit bought in the UK at the agreed price, with the supplier\'s photos. Add it to a shipment to bring it to Accra.')
                    ->fillForm(fn (SupplierOffer $record) => [
                        'agreed_price' => (string) $record->asking_price->getAmount(),
                        'location_id' => Location::where('type', LocationType::Buying)->value('id'),
                    ])
                    ->schema([
                        Select::make('product_id')
                            ->label('Catalogue product')
                            ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Leave empty to create a new product from the offer.'),
                        MoneyInput::withCedis(MoneyInput::make('agreed_price', $currency)->label('Agreed price (what we pay)')->required(), $currency),
                        MoneyInput::withCedis(MoneyInput::make('list_price', $currency)->label('List price in the shop')->required(), $currency, 'Customers see '),
                        Select::make('location_id')
                            ->label('Bought at')
                            ->options(fn () => Location::where('type', LocationType::Buying)->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (SupplierOffer $record, array $data, SupplierOfferService $offers): void {
                        /** @var User $user */
                        $user = auth()->user();

                        try {
                            $unit = $offers->accept($record, $user, $data);
                        } catch (OfferRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title("Bought as {$unit->reference}")->body('The supplier has been told.')->send();
                    }),
                Action::make('decline')
                    ->label('Decline')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (SupplierOffer $record) => $record->status === OfferStatus::Submitted)
                    ->schema([
                        Textarea::make('reason')->label('Reason (shown to the supplier)')->required()->maxLength(500),
                    ])
                    ->action(function (SupplierOffer $record, array $data, SupplierOfferService $offers): void {
                        /** @var User $user */
                        $user = auth()->user();

                        try {
                            $offers->decline($record, $user, (string) $data['reason']);
                        } catch (OfferRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title("{$record->reference} declined")->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierOffers::route('/'),
        ];
    }
}
