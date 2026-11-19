<?php

namespace App\Filament\Resources\StockUnits\Tables;

use App\Enums\StockStatus;
use App\Exceptions\InvalidStockTransition;
use App\Models\StockUnit;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->searchable()->weight('bold'),
                TextColumn::make('product.name')
                    ->searchable()
                    ->hidden(fn ($livewire) => $livewire instanceof RelationManager),
                TextColumn::make('condition.grade')->label('Grade'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('price')
                    ->label('List price')
                    ->formatStateUsing(fn (Money $state) => $state->formatToLocale('en_GB'))
                    ->sortable(['price_amount']),
                TextColumn::make('cost')
                    ->formatStateUsing(fn (Money $state) => $state->formatToLocale('en_GB'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location.name')->toggleable(),
                TextColumn::make('supplier.name')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('serial_number')->label('Serial / IMEI')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchased_at')->label('Bought')->date()->sortable(),
                TextColumn::make('received_at')->label('In store since')->date()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(StockStatus::class)->multiple(),
                SelectFilter::make('condition')->relationship('condition', 'name'),
                SelectFilter::make('location')->relationship('location', 'name'),
            ])
            ->recordActions([
                self::changeStatusAction(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /**
     * Moves a unit along its lifecycle. Selling is excluded: sales are recorded
     * through the in-store sale screen so the negotiated price is captured.
     */
    public static function changeStatusAction(): Action
    {
        return Action::make('changeStatus')
            ->label('Move')
            ->icon(Heroicon::OutlinedArrowRightCircle)
            ->visible(fn (StockUnit $record) => self::manualTransitions($record) !== [])
            ->schema(fn (StockUnit $record) => [
                Select::make('status')
                    ->label('Move to')
                    ->options(collect(self::manualTransitions($record))
                        ->mapWithKeys(fn (StockStatus $status) => [$status->value => $status->getLabel()])
                        ->all())
                    ->required(),
            ])
            ->action(function (StockUnit $record, array $data): void {
                try {
                    $record->transitionTo(StockStatus::from($data['status']));
                } catch (InvalidStockTransition $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title("{$record->reference} is now {$record->status->getLabel()}")->send();
            });
    }

    /**
     * @return list<StockStatus>
     */
    private static function manualTransitions(StockUnit $record): array
    {
        return array_values(array_filter(
            $record->status->allowedTransitions(),
            fn (StockStatus $status) => $status !== StockStatus::Sold,
        ));
    }
}
