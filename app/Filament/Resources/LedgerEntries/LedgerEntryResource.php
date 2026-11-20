<?php

namespace App\Filament\Resources\LedgerEntries;

use App\Enums\TransactionCategory;
use App\Filament\Resources\LedgerEntries\Pages\ManageLedgerEntries;
use App\Filament\Support\MoneyInput;
use App\Models\LedgerEntry;
use App\Models\TransactionType;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LedgerEntryResource extends Resource
{
    protected static ?string $model = LedgerEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Ledger';

    protected static ?string $modelLabel = 'ledger entry';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('occurred_on')
                    ->label('Date')
                    ->native(false)
                    ->default(today())
                    ->maxDate(now())
                    ->required(),
                Select::make('transaction_type_id')
                    ->label('Type')
                    ->options(fn () => self::typeOptions())
                    ->searchable()
                    ->required(),
                TextInput::make('description')->required()->maxLength(255)->columnSpanFull(),
                Select::make('currency')
                    ->options(self::currencies())
                    ->default((string) config('shop.display_currency'))
                    ->live()
                    ->required()
                    ->dehydrated(false),
                MoneyInput::make('money', fn (Get $get) => (string) ($get('currency') ?: config('shop.display_currency')))
                    ->label('Amount')
                    ->minValue(null)
                    ->helperText('Use a minus sign to reduce a balance, e.g. paying back part of a loan.')
                    ->required(),
                Select::make('location_id')
                    ->label('Store or base')
                    ->relationship('location', 'name'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_on', 'desc')
            ->columns([
                TextColumn::make('occurred_on')->label('Date')->date('j M Y')->sortable(),
                TextColumn::make('transactionType.name')->label('Type')->searchable(),
                TextColumn::make('transactionType.category')
                    ->label('Group')
                    ->formatStateUsing(fn (TransactionCategory $state) => $state->label())
                    ->toggleable(),
                TextColumn::make('description')->searchable()->wrap(),
                TextColumn::make('money')
                    ->label('Amount')
                    ->alignEnd()
                    ->formatStateUsing(fn (LedgerEntry $record) => $record->money->formatToLocale((string) config('shop.display_locale')))
                    ->color(fn (LedgerEntry $record) => $record->money->isNegative() ? 'danger' : null),
                TextColumn::make('recorder.name')->label('Entered by')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('transaction_type_id')
                    ->label('Type')
                    ->options(fn () => self::typeOptions()),
                Filter::make('occurred_on')
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('until')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('occurred_on', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('occurred_on', '<=', $date))),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /**
     * Types the owner can enter, grouped for the select. Stock and retained
     * earnings are worked out by the reports, so they aren't offered.
     *
     * @return array<string, array<int, string>>
     */
    public static function typeOptions(): array
    {
        return TransactionType::query()
            ->where('derived_in_reports', false)
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (TransactionType $type) => $type->category->label())
            ->map(fn ($types) => $types->pluck('name', 'id')->all())
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function currencies(): array
    {
        $display = (string) config('shop.display_currency');
        $list = (string) config('shop.list_currency');

        return [$display => $display, $list => $list];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLedgerEntries::route('/'),
        ];
    }
}
