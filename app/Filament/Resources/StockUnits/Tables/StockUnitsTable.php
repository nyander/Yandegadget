<?php

namespace App\Filament\Resources\StockUnits\Tables;

use App\Enums\StockStatus;
use App\Exceptions\InvalidStockTransition;
use App\Filament\Resources\StockBatches\StockBatchResource;
use App\Filament\Support\MoneyInput;
use App\Models\Condition;
use App\Models\StockUnit;
use App\Services\StockIntake;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
                TextColumn::make('batch.reference')
                    ->label('Bought as')
                    ->badge()
                    ->color('info')
                    ->placeholder('One-off')
                    ->url(fn (StockUnit $record) => $record->batch_id ? StockBatchResource::getUrl('edit', ['record' => $record->batch_id]) : null),
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
                TernaryFilter::make('bulk')
                    ->label('Bought as')
                    ->placeholder('Bulk and one-off')
                    ->trueLabel('Bulk only')
                    ->falseLabel('One-off only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('batch_id'),
                        false: fn (Builder $query) => $query->whereNull('batch_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->toolbarActions([
                self::editSelectedAction(),
            ])
            ->recordActions([
                self::changeStatusAction(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /**
     * Changes several selected items at once. Leave a box empty to keep each item's own value.
     */
    public static function editSelectedAction(): BulkAction
    {
        $currency = (string) config('shop.list_currency');

        return BulkAction::make('editSelected')
            ->label('Edit selected')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->modalHeading('Edit the selected items')
            ->modalDescription('Fill in only what you want to change; empty boxes are left as they are. Sold items aren\'t changed. To change a whole bulk purchase, edit it under Bulk stock instead.')
            ->schema([
                Select::make('condition_id')
                    ->label('Condition')
                    ->options(fn () => Condition::query()->orderBy('sort_order')->get()->mapWithKeys(fn (Condition $c) => [$c->id => "{$c->grade} – {$c->name}"])),
                MoneyInput::withCedis(MoneyInput::make('price', $currency)->label('List price of each'), $currency, 'Customers see '),
                MoneyInput::withCedis(MoneyInput::make('cost', $currency)->label('Cost of each'), $currency),
                Textarea::make('condition_notes')->label('Notes')->rows(2),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data, StockIntake $intake): void {
                $updated = $intake->updateUnits($records->filter(fn ($record) => $record instanceof StockUnit), $data);

                Notification::make()
                    ->title($updated === 0 ? 'Nothing changed' : "{$updated} ".str('item')->plural($updated).' updated')
                    ->{$updated === 0 ? 'warning' : 'success'}()
                    ->send();
            });
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
