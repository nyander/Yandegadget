<?php

namespace App\Filament\Resources\ProductRequests;

use App\Enums\CancellationCause;
use App\Enums\RequestStatus;
use App\Enums\StockStatus;
use App\Exceptions\RequestRuleViolation;
use App\Filament\Resources\ProductRequests\Pages\ListProductRequests;
use App\Filament\Resources\ProductRequests\Pages\ViewProductRequest;
use App\Filament\Resources\StockUnits\StockUnitResource;
use App\Models\ProductRequest;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\Payments\GatewayException;
use App\Services\ProductRequestService;
use App\Services\RefundPolicy;
use App\Services\ShopSettings;
use BackedEnum;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
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

class ProductRequestResource extends Resource
{
    protected static ?string $model = ProductRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static ?string $modelLabel = 'customer request';

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
            ->recordUrl(fn (ProductRequest $record) => self::getUrl('view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Open'),
                    self::matchAction(),
                    self::declineAction(),
                    self::cancelAction(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $date = fn (string $name, string $label) => TextEntry::make($name)->label($label)->dateTime('j M Y, H:i')->placeholder('Not yet');

        return $schema->columns(1)->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Section::make('What they want')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->columnSpan(['lg' => 2])
                    ->columns(2)
                    ->schema([
                        TextEntry::make('description')->label('Item')->weight('bold')->columnSpanFull(),
                        TextEntry::make('category.name')->label('Category'),
                        TextEntry::make('condition.name')->label('Lowest condition they\'ll accept')
                            ->formatStateUsing(fn (ProductRequest $record) => "Grade {$record->condition->grade} · {$record->condition->name}")
                            ->helperText(fn (ProductRequest $record) => $record->condition->description ?? null),
                        TextEntry::make('notes')->label('Their notes')->placeholder('No notes')->columnSpanFull(),
                    ]),
                Section::make('Status')
                    ->icon(Heroicon::OutlinedFlag)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('status')->hiddenLabel()->badge()->size('lg'),
                        TextEntry::make('next')->hiddenLabel()->state(fn (ProductRequest $record) => self::nextStep($record))->color('gray'),
                        TextEntry::make('cancel_reason')->label('Why it was cancelled')
                            ->visible(fn (ProductRequest $record) => $record->status === RequestStatus::Cancelled)
                            ->helperText(fn (ProductRequest $record) => match ($record->cancelled_by) {
                                CancellationCause::Shop => 'Cancelled by the shop',
                                CancellationCause::Customer => 'Cancelled by the customer',
                                default => null,
                            })
                            ->placeholder('No reason given'),
                    ]),
            ]),
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Section::make('Customer')
                    ->icon(Heroicon::OutlinedUser)
                    ->schema([
                        TextEntry::make('customer.name')->label('Name'),
                        TextEntry::make('customer.email')->label('Email')->copyable()
                            ->url(fn (ProductRequest $record) => 'mailto:'.$record->customer->email),
                    ]),
                Section::make('Deposit')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextEntry::make('deposit_paid')->label('Amount')
                            ->state(fn (ProductRequest $record) => ($record->latestPayment === null ? $record->deposit : $record->latestPayment->money)->formatToLocale((string) config('shop.display_locale')))
                            ->helperText(fn (ProductRequest $record) => $record->latestPayment === null ? 'Not paid yet' : $record->latestPayment->status->getLabel()),
                        TextEntry::make('refunded')->label('Refunded')
                            ->visible(fn (ProductRequest $record) => $record->latestPayment !== null && $record->latestPayment->refunded_amount > 0)
                            ->state(fn (ProductRequest $record) => $record->latestPayment === null ? null
                                : Money::ofMinor($record->latestPayment->refunded_amount, $record->latestPayment->currency)->formatToLocale((string) config('shop.display_locale'))),
                    ]),
                Section::make('Item found for them')
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        TextEntry::make('stockUnit.reference')->label('Stock unit')
                            ->placeholder('Nothing matched yet')
                            ->url(fn (ProductRequest $record) => $record->stockUnit === null ? null : StockUnitResource::getUrl('edit', ['record' => $record->stockUnit]))
                            ->color('primary'),
                        TextEntry::make('stockUnit.product.name')->label('Product')
                            ->visible(fn (ProductRequest $record) => $record->stockUnit !== null),
                        TextEntry::make('stockUnit.status')->label('Where it is')->badge()
                            ->visible(fn (ProductRequest $record) => $record->stockUnit !== null),
                    ]),
            ]),
            Section::make('What\'s happened')
                ->icon(Heroicon::OutlinedClock)
                ->columns(['default' => 2, 'lg' => 3])
                ->schema([
                    $date('created_at', 'Asked'),
                    $date('paid_at', 'Deposit paid'),
                    $date('sourced_at', 'Item found'),
                    $date('ready_at', 'Ready in store'),
                    $date('fulfilled_at', 'Collected'),
                    $date('cancelled_at', 'Cancelled')->visible(fn (ProductRequest $record) => $record->cancelled_at !== null),
                ]),
        ]);
    }

    /** One plain sentence on what to do next, for the list and the details page. */
    public static function nextStep(ProductRequest $request): string
    {
        $days = app(ShopSettings::class)->collectionDays();

        return match ($request->status) {
            RequestStatus::AwaitingDeposit => 'The customer hasn\'t paid the deposit yet. Nothing to do until they do.',
            RequestStatus::Open => 'Deposit paid. Look for the item, then use Match an item. If you can\'t get it, Decline and the customer gets all of the deposit back.',
            RequestStatus::Sourced => 'Found and on its way from the UK. It\'s reserved for the customer when staff check it in.',
            RequestStatus::ReadyForCollection => "Reserved in the store. The customer has {$days} days to collect it; staff sell it to them and the deposit comes off the price.",
            RequestStatus::Fulfilled => 'Done: the customer bought the item.',
            RequestStatus::Cancelled => 'Cancelled. Any refund has gone back the way the customer paid.',
        };
    }

    public static function matchAction(): Action
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

    /** Turn down a request the shop can't supply: a full refund, no fee. */
    public static function declineAction(): Action
    {
        return Action::make('decline')
            ->label('Decline')
            ->icon(Heroicon::OutlinedHandThumbDown)
            ->color('danger')
            ->visible(fn (ProductRequest $record) => $record->status === RequestStatus::Open)
            ->modalHeading('Decline this request?')
            ->modalDescription(fn (ProductRequest $record) => 'Use this when you can\'t get the item. '.self::refundSummary($record, CancellationCause::Shop).' The customer is told by email with your reason.')
            ->modalSubmitActionLabel('Decline and refund')
            ->schema([
                Textarea::make('reason')
                    ->label('Reason (shown to the customer)')
                    ->placeholder('For example: we couldn\'t find one in this condition at a fair price.')
                    ->required()
                    ->maxLength(500),
            ])
            ->action(function (ProductRequest $record, array $data, ProductRequestService $requests): void {
                /** @var User $user */
                $user = auth()->user();

                try {
                    $requests->cancel($record, $user, (string) $data['reason'], CancellationCause::Shop);
                } catch (RequestRuleViolation|GatewayException $e) {
                    Notification::make()->danger()->title('Not declined')->body($e->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title("{$record->reference} declined; the customer has been told")->send();
            });
    }

    public static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (ProductRequest $record) => ! in_array($record->status, [RequestStatus::Fulfilled, RequestStatus::Cancelled], true))
            ->modalDescription('Any reserved item goes back on sale. The customer is told by email, and any refund goes back through Paystack.')
            ->schema(fn (ProductRequest $record) => [
                Radio::make('cause')
                    ->label('Who is cancelling?')
                    ->options([
                        CancellationCause::Shop->value => 'The shop: we can\'t supply it, or it isn\'t as described',
                        CancellationCause::Customer->value => 'The customer changed their mind',
                    ])
                    ->descriptions([
                        CancellationCause::Shop->value => self::refundSummary($record, CancellationCause::Shop),
                        CancellationCause::Customer->value => self::refundSummary($record, CancellationCause::Customer),
                    ])
                    ->default(CancellationCause::Shop->value)
                    ->required(),
                Textarea::make('reason')
                    ->label('Reason (shown to the customer)')
                    ->required()
                    ->maxLength(500),
            ])
            ->action(function (ProductRequest $record, array $data, ProductRequestService $requests): void {
                /** @var User $user */
                $user = auth()->user();

                try {
                    $requests->cancel($record, $user, (string) $data['reason'], CancellationCause::from((string) $data['cause']));
                } catch (RequestRuleViolation|GatewayException $e) {
                    Notification::make()->danger()->title('Not cancelled')->body($e->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title("{$record->reference} cancelled")->send();
            });
    }

    private static function refundSummary(ProductRequest $record, CancellationCause $cause): string
    {
        $quote = app(RefundPolicy::class)->quote($record, $cause);

        if ($quote === null) {
            return 'No deposit has been paid, so there is nothing to refund.';
        }

        $locale = (string) config('shop.display_locale');

        if ($quote->isFull()) {
            return "Refund the full deposit of {$quote->paid->formatToLocale($locale)}.";
        }

        return "Refund {$quote->refund->formatToLocale($locale)} and keep {$quote->kept->formatToLocale($locale)} ({$quote->feePercent}% cancellation fee, because the item has been found).";
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductRequests::route('/'),
            'view' => ViewProductRequest::route('/{record}'),
        ];
    }
}
