<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Enums\PaymentMethod;
use App\Exceptions\SaleRuleViolation;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        $money = fn (Money $state) => $state->formatToLocale('en_GH');

        return $table
            ->defaultSort('sold_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['stockUnit.product', 'stockUnit.condition', 'seller', 'location']))
            ->columns([
                TextColumn::make('reference')->searchable()->weight('bold')
                    ->description(fn (Sale $record) => $record->isVoided() ? 'Void' : null),
                TextColumn::make('sold_at')->label('Sold')->dateTime()->sortable(),
                TextColumn::make('stockUnit.product.name')->label('Item')->searchable()
                    ->description(fn (Sale $record) => "{$record->stockUnit->reference} · Grade {$record->stockUnit->condition->grade}"),
                TextColumn::make('listed')->label('Listed')->formatStateUsing($money),
                TextColumn::make('price_paid')->label('Paid')->formatStateUsing($money)->weight('bold')
                    ->sortable(['price_paid_amount']),
                TextColumn::make('discount')->label('Discount')
                    ->state(fn (Sale $record) => $record->discountPercent().'%'),
                TextColumn::make('payment_method')->label('Payment')->badge(),
                TextColumn::make('seller.name')->label('Sold by'),
                TextColumn::make('customer_name')->label('Customer')->placeholder('—')->toggleable(),
                TextColumn::make('rate')->label('Rate used')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location.name')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('void_reason')->label('Void reason')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('voided')
                    ->label('Status')
                    ->placeholder('Completed sales')
                    ->trueLabel('Void sales')
                    ->falseLabel('All sales')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('voided_at'),
                        false: fn (Builder $query) => $query,
                        blank: fn (Builder $query) => $query->whereNull('voided_at'),
                    ),
                SelectFilter::make('payment_method')->options(PaymentMethod::class),
                Filter::make('today')->label('Today')->query(fn (Builder $query) => $query->whereDate('sold_at', today())),
            ])
            ->recordActions([
                Action::make('void')
                    ->label('Void')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->visible(function (Sale $record): bool {
                        /** @var User|null $user */
                        $user = auth()->user();

                        return (bool) $user?->can('void', $record);
                    })
                    ->modalDescription('The item goes back into the store and this sale is kept on record, marked void.')
                    ->schema([
                        Textarea::make('reason')->label('Reason')->required()->maxLength(500),
                    ])
                    ->action(function (Sale $record, array $data, SaleService $sales): void {
                        /** @var User $user */
                        $user = auth()->user();

                        try {
                            $sales->void($record, $user, (string) $data['reason']);
                        } catch (SaleRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title("{$record->reference} voided")->send();
                    }),
            ]);
    }
}
