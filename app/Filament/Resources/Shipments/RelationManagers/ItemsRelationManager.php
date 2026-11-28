<?php

namespace App\Filament\Resources\Shipments\RelationManagers;

use App\Enums\ReceiptOutcome;
use App\Enums\ShipmentStatus;
use App\Enums\StockStatus;
use App\Exceptions\ShipmentRuleViolation;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\StockBatch;
use App\Models\StockUnit;
use App\Services\ShipmentService;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Shipment $ownerRecord
 */
class ItemsRelationManager extends RelationManager
{
    /** Loaded with the page, so the page tour can point at it. */
    protected static bool $isLazy = false;

    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * Bulk purchases with items ready to ship, e.g. "BT-0003 · Apple Watch Series 8 ×50 · Grade A".
     *
     * @return array<int, string>
     */
    private function readyBatches(): array
    {
        return StockBatch::query()
            ->with(['product', 'condition'])
            ->withCount(['units as ready_count' => fn (Builder $q) => $q->where('status', StockStatus::Purchased)
                ->whereDoesntHave('shipmentItems', fn (Builder $items) => $items->whereHas('shipment', fn (Builder $shipment) => $shipment->whereIn('status', [ShipmentStatus::Draft, ShipmentStatus::InTransit])))])
            ->latest()
            ->get()
            ->filter(fn (StockBatch $batch) => (int) $batch->getAttribute('ready_count') > 0)
            ->mapWithKeys(fn (StockBatch $batch) => [$batch->id => "{$batch->reference} · {$batch->product->name} ×{$batch->getAttribute('ready_count')} · Grade {$batch->condition->grade}"])
            ->all();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stockUnit.reference')
            ->modifyQueryUsing(fn ($query) => $query->with(['stockUnit.product', 'stockUnit.condition', 'stockUnit.batch', 'checker']))
            // Bulk purchases show as one group ("BT-0003 · Apple Watch ×50"); one-off items together.
            ->defaultGroup(
                Group::make('stockUnit.batch_id')
                    ->label('Bought as')
                    ->getTitleFromRecordUsing(fn (ShipmentItem $record) => $record->stockUnit->batch
                        ? "{$record->stockUnit->batch->reference} · {$record->stockUnit->product->name} (bulk)"
                        : 'One-off items')
                    ->collapsible(),
            )
            ->columns([
                TextColumn::make('stockUnit.reference')->label('Reference')->weight('bold')->searchable(),
                TextColumn::make('stockUnit.product.name')->label('Product')->searchable(),
                TextColumn::make('stockUnit.condition.grade')->label('Grade'),
                TextColumn::make('stockUnit.cost')
                    ->label('Cost')
                    ->formatStateUsing(fn (Money $state) => $state->formatToLocale('en_GB')),
                TextColumn::make('outcome')
                    ->badge()
                    ->placeholder(fn () => $this->ownerRecord->isDraft() ? '—' : 'Waiting'),
                TextColumn::make('receipt_notes')->label('Notes')->wrap()->placeholder('—'),
                SpatieMediaLibraryImageColumn::make('arrival_photos')
                    ->collection('arrival_photos')
                    ->conversion('thumb')
                    ->label('Photos')
                    ->stacked()
                    ->limit(3),
                TextColumn::make('checker.name')->label('Checked by')->placeholder('—'),
                TextColumn::make('checked_at')->label('Checked')->dateTime()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('outcome')->options(ReceiptOutcome::class),
            ])
            ->headerActions([
                Action::make('addItems')
                    ->label('Add items')
                    ->icon(Heroicon::OutlinedPlus)
                    ->visible(fn () => $this->ownerRecord->isDraft())
                    ->modalDescription('Pick whole bulk purchases (every item in them goes in at once), single items, or both. Only items bought and not already on a shipment are listed.')
                    ->schema([
                        Select::make('batch_ids')
                            ->label('Bulk purchases')
                            ->multiple()
                            ->searchable()
                            ->options(fn () => $this->readyBatches())
                            ->helperText('e.g. "Apple Watch ×50": adds all 50 in one go.'),
                        Select::make('stock_unit_ids')
                            ->label('Single items')
                            ->multiple()
                            ->searchable()
                            ->options(fn () => StockUnit::readyToShip()
                                ->whereNull('batch_id')
                                ->with(['product', 'condition', 'location'])
                                ->latest()
                                ->get()
                                ->sortByDesc(fn (StockUnit $unit) => $unit->location_id === $this->ownerRecord->origin_location_id)
                                ->mapWithKeys(fn (StockUnit $unit) => [$unit->id => "{$unit->reference} · {$unit->product->name} · {$unit->condition->grade} · {$unit->location->name}"])),
                    ])
                    ->action(function (array $data, ShipmentService $shipments): void {
                        /** @var list<int|string> $picked */
                        $picked = $data['stock_unit_ids'] ?? [];
                        $ids = collect($picked)
                            ->map(fn (int|string $id) => (int) $id)
                            ->merge(StockUnit::readyToShip()->whereIn('batch_id', $data['batch_ids'] ?? [])->pluck('id')->map(fn (mixed $id) => (int) $id))
                            ->unique()
                            ->values();

                        if ($ids->isEmpty()) {
                            Notification::make()->warning()->title('Pick at least one bulk purchase or item')->send();

                            return;
                        }

                        try {
                            $added = $shipments->addUnits($this->ownerRecord, $ids);
                        } catch (ShipmentRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title("{$added} ".str('item')->plural($added).' added')->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('removeSelected')
                    ->label('Remove selected')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () => $this->ownerRecord->isDraft())
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, ShipmentService $shipments): void {
                        try {
                            $records->each(fn ($item) => $item instanceof ShipmentItem ? $shipments->removeItem($item) : null);
                        } catch (ShipmentRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label('Remove')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () => $this->ownerRecord->isDraft())
                    ->action(function (ShipmentItem $record, ShipmentService $shipments): void {
                        try {
                            $shipments->removeItem($record);
                        } catch (ShipmentRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),
            ]);
    }
}
