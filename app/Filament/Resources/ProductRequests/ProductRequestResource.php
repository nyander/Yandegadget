<?php

namespace App\Filament\Resources\ProductRequests;

use App\Enums\RequestStatus;
use App\Enums\StockStatus;
use App\Exceptions\RequestRuleViolation;
use App\Filament\Resources\ProductRequests\Pages\ListProductRequests;
use App\Models\ProductRequest;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\Payments\GatewayException;
use App\Services\ProductRequestService;
use BackedEnum;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductRequestResource extends Resource
{
    protected static ?string $model = ProductRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Customer requests';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationBadge(): ?string
    {
        $open = ProductRequest::where('status', RequestStatus::Open)->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'category', 'condition', 'stockUnit.product', 'latestPayment']))
            ->columns([
                TextColumn::make('reference')->searchable()->weight('bold'),
                TextColumn::make('status')->badge(),
                TextColumn::make('description')->label('Wants')->searchable()->wrap()
                    ->description(fn (ProductRequest $record) => "{$record->category->name} · Grade {$record->condition->grade} or better"),
                TextColumn::make('customer.name')->label('Customer')->searchable()
                    ->description(fn (ProductRequest $record) => $record->customer->email),
                TextColumn::make('latestPayment.money')->label('Deposit')
                    ->formatStateUsing(fn (?Money $state) => $state?->formatToLocale('en_GH'))
                    ->description(fn (ProductRequest $record) => $record->latestPayment?->status->getLabel())
                    ->placeholder('—'),
                TextColumn::make('stockUnit.reference')->label('Matched item')
                    ->description(fn (ProductRequest $record) => $record->stockUnit?->product->name)
                    ->placeholder('—'),
                TextColumn::make('notes')->wrap()->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Requested')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(RequestStatus::class)->multiple()
                    ->default([RequestStatus::Open->value, RequestStatus::Sourced->value, RequestStatus::ReadyForCollection->value]),
                SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::matchAction(),
                    self::cancelAction(),
                ]),
            ]);
    }

    private static function matchAction(): Action
    {
        return Action::make('match')
            ->label('Match an item')
            ->icon(Heroicon::OutlinedLink)
            ->visible(fn (ProductRequest $record) => $record->status === RequestStatus::Open)
            ->modalDescription('Pick the stock unit you bought for this customer. If it\'s already in the store it is reserved now; otherwise it\'s reserved when it\'s checked in. The customer is told either way.')
            ->schema([
                Select::make('stock_unit_id')
                    ->label('Stock unit')
                    ->required()
                    ->searchable()
                    ->options(fn (ProductRequest $record) => StockUnit::query()
                        ->whereIn('status', [StockStatus::Purchased, StockStatus::InTransit, StockStatus::Received])
                        ->whereDoesntHave('productRequest', fn (Builder $q) => $q->whereIn('status', [RequestStatus::Sourced, RequestStatus::ReadyForCollection]))
                        ->with(['product', 'condition'])
                        ->get()
                        ->sortByDesc(fn (StockUnit $unit) => $unit->product->category_id === $record->category_id)
                        ->mapWithKeys(fn (StockUnit $unit) => [$unit->id => "{$unit->reference} · {$unit->product->name} · Grade {$unit->condition->grade} · {$unit->status->getLabel()}"])),
            ])
            ->action(function (ProductRequest $record, array $data, ProductRequestService $requests): void {
                try {
                    $requests->source($record, StockUnit::whereKey($data['stock_unit_id'])->firstOrFail());
                } catch (RequestRuleViolation $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title("{$record->reference} matched; the customer has been told")->send();
            });
    }

    private static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (ProductRequest $record) => ! in_array($record->status, [RequestStatus::Fulfilled, RequestStatus::Cancelled], true))
            ->modalDescription('Any reserved item goes back on sale. The customer is told by email.')
            ->schema(fn (ProductRequest $record) => [
                Textarea::make('reason')
                    ->label('Reason (shown to the customer)')
                    ->required()
                    ->maxLength(500),
                Toggle::make('refund')
                    ->label('Refund the deposit through Paystack')
                    ->default(true)
                    ->visible($record->paid_at !== null),
            ])
            ->action(function (ProductRequest $record, array $data, ProductRequestService $requests): void {
                /** @var User $user */
                $user = auth()->user();

                try {
                    $requests->cancel($record, $user, (string) $data['reason'], (bool) ($data['refund'] ?? false));
                } catch (RequestRuleViolation|GatewayException $e) {
                    Notification::make()->danger()->title('Not cancelled')->body($e->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title("{$record->reference} cancelled")->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductRequests::route('/'),
        ];
    }
}
