<?php

namespace App\Filament\Resources\TransactionTypes;

use App\Enums\TransactionCategory;
use App\Filament\Resources\TransactionTypes\Pages\ManageTransactionTypes;
use App\Models\TransactionType;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use UnitEnum;

class TransactionTypeResource extends Resource
{
    protected static ?string $model = TransactionType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Ledger types';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255)->unique(ignoreRecord: true),
                Select::make('category')
                    ->label('Shows on')
                    ->options(collect(TransactionCategory::cases())->mapWithKeys(fn (TransactionCategory $c) => [$c->value => $c->label()]))
                    ->required(),
                Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->defaultGroup(Group::make('category')
                ->label('Shows on')
                ->getTitleFromRecordUsing(fn (TransactionType $record) => $record->category->label()))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('description')->wrap()->placeholder('—'),
                TextColumn::make('ledger_entries_count')->label('Entries')->counts('ledgerEntries'),
            ])
            ->recordActions([
                EditAction::make()->hidden(fn (TransactionType $record) => $record->derived_in_reports),
                DeleteAction::make()->hidden(fn (TransactionType $record) => $record->derived_in_reports
                    || $record->ledgerEntries()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransactionTypes::route('/'),
        ];
    }
}
