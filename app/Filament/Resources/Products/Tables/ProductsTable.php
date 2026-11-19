<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\StockStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'stockUnits as in_store_count' => fn (Builder $q) => $q->whereIn('status', StockStatus::customerVisibleValues()),
                'stockUnits as on_the_way_count' => fn (Builder $q) => $q->whereIn('status', [StockStatus::Purchased->value, StockStatus::InTransit->value]),
            ]))
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->collection('gallery')
                    ->conversion('thumb')
                    ->limit(1)
                    ->label(''),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->brand),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('in_store_count')->label('In store')->badge()->color('success'),
                TextColumn::make('on_the_way_count')->label('On the way')->badge()->color('info'),
                IconColumn::make('is_published')->label('Published')->boolean(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
