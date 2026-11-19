<?php

namespace App\Filament\Resources\Shipments\RelationManagers;

use App\Enums\ReceiptOutcome;
use App\Exceptions\ShipmentRuleViolation;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\StockUnit;
use App\Services\ShipmentService;
use Brick\Money\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * @property Shipment $ownerRecord
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stockUnit.reference')
            ->modifyQueryUsing(fn ($query) => $query->with(['stockUnit.product', 'stockUnit.condition', 'checker']))
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
                    ->schema([
                        Select::make('stock_unit_ids')
                            ->label('Items bought in the UK')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->options(fn () => StockUnit::readyToShip()
                                ->with(['product', 'condition'])
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn (StockUnit $unit) => [$unit->id => "{$unit->reference} · {$unit->product->name} · {$unit->condition->grade}"]))
                            ->helperText('Only purchased items that are not already on a shipment are listed.'),
                    ])
                    ->action(function (array $data, ShipmentService $shipments): void {
                        try {
                            $added = $shipments->addUnits($this->ownerRecord, $data['stock_unit_ids']);
                        } catch (ShipmentRuleViolation $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title("{$added} item(s) added")->send();
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
