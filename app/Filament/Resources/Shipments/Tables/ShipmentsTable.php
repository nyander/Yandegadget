<?php

namespace App\Filament\Resources\Shipments\Tables;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Brick\Money\Money;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'items',
                'items as checked_count' => fn (Builder $q) => $q->whereNotNull('checked_at'),
            ]))
            ->columns([
                TextColumn::make('reference')->searchable()->weight('bold'),
                TextColumn::make('status')->badge(),
                TextColumn::make('items_count')->label('Items'),
                TextColumn::make('progress')
                    ->label('Checked in')
                    ->state(fn (Shipment $record) => $record->isDraft() ? '—' : "{$record->getAttribute('checked_count')} / {$record->getAttribute('items_count')}"),
                TextColumn::make('destination.name')->label('To'),
                TextColumn::make('carrier.name')->placeholder('—'),
                TextColumn::make('cost')
                    ->formatStateUsing(fn (?Money $state) => $state?->formatToLocale('en_GB'))
                    ->placeholder('—'),
                TextColumn::make('dispatched_on')->label('Dispatched')->date()->sortable()->placeholder('—'),
                TextColumn::make('expected_on')->label('Expected')->date()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->options(ShipmentStatus::class),
            ])
            ->recordActions([
                EditAction::make()->label(fn (Shipment $record) => $record->isDraft() ? 'Edit' : 'View'),
            ]);
    }
}
