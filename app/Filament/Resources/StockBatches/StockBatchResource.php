<?php

namespace App\Filament\Resources\StockBatches;

use App\Enums\StockStatus;
use App\Filament\Resources\StockBatches\Pages\EditStockBatch;
use App\Filament\Resources\StockBatches\Pages\ListStockBatches;
use App\Filament\Support\MoneyInput;
use App\Models\Condition;
use App\Models\StockBatch;
use App\Models\Supplier;
use BackedEnum;
use Brick\Money\Money;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Items bought in bulk (e.g. 50 watches), kept as a batch so they can be
 * edited, shipped and checked in together, and shown as one line in the shop.
 * New batches come from "Add several". One-off and second-hand items aren't here.
 */
class StockBatchResource extends Resource
{
    protected static ?string $model = StockBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Bulk stock';

    protected static ?string $modelLabel = 'bulk batch';

    protected static ?string $pluralModelLabel = 'bulk stock';

    protected static ?string $slug = 'bulk-stock';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        $currency = (string) config('shop.list_currency');

        return $schema->components([
            Section::make('Shared details')
                ->description(fn (?StockBatch $record) => $record === null ? null : sprintf(
                    'Saving updates all %d of the %d items not yet sold. Sold items keep what they had, so past sales don\'t change.',
                    $record->unsoldUnits()->count(),
                    $record->quantity,
                ))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('condition_id')
                        ->label('Condition')
                        ->options(fn () => Condition::query()->orderBy('sort_order')->get()->mapWithKeys(fn (Condition $c) => [$c->id => "{$c->grade} – {$c->name}"]))
                        ->required(),
                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(fn () => Supplier::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    MoneyInput::withCedis(MoneyInput::make('cost', $currency)->label('Cost of each')->required(), $currency),
                    MoneyInput::withCedis(MoneyInput::make('price', $currency)->label('List price of each')->required(), $currency, 'Customers see '),
                    DatePicker::make('purchased_at')->label('Bought on')->maxDate(now())->required(),
                    Textarea::make('condition_notes')->label('Notes for all of them')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $gbp = fn (Money $state) => $state->formatToLocale('en_GB');
        $count = fn (StockStatus ...$statuses) => fn (StockBatch $record) => $record->units->whereIn('status', $statuses)->count();

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product', 'condition', 'units:id,batch_id,status']))
            ->columns([
                TextColumn::make('reference')->weight('bold')->searchable(),
                TextColumn::make('product.name')->label('Product')->searchable()->wrap(),
                TextColumn::make('condition.grade')->label('Grade'),
                TextColumn::make('quantity')->label('Bought')->numeric(),
                TextColumn::make('in_uk')->label('Not shipped yet')->state($count(StockStatus::Purchased))->color('gray'),
                TextColumn::make('on_way')->label('On the way')->state($count(StockStatus::InTransit))->color('info'),
                TextColumn::make('in_store')->label('In store')->state($count(StockStatus::Received))->color('success')->weight('bold'),
                TextColumn::make('reserved')->label('Reserved')->state($count(StockStatus::Reserved))->color('warning'),
                TextColumn::make('sold')->label('Sold')->state($count(StockStatus::Sold)),
                TextColumn::make('cost')->label('Cost each')->formatStateUsing($gbp),
                TextColumn::make('price')->label('Price each')->formatStateUsing($gbp),
                TextColumn::make('purchased_at')->label('Bought on')->date()->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Edit all'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockBatches::route('/'),
            'edit' => EditStockBatch::route('/{record}/edit'),
        ];
    }
}
