<?php

namespace App\Filament\Resources\StaffWages;

use App\Enums\WagePeriod;
use App\Filament\Resources\StaffWages\Pages\ManageStaffWages;
use App\Filament\Support\MoneyInput;
use App\Models\StaffWage;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class StaffWageResource extends Resource
{
    protected static ?string $model = StaffWage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Staff wages';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Staff account')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                        if ($state !== null && blank($get('name'))) {
                            $set('name', User::query()->whereKey($state)->value('name'));
                        }
                    })
                    ->helperText('Optional. Not everyone on the payroll has a login.'),
                TextInput::make('name')->label('Name on the payroll')->required()->maxLength(255),
                Select::make('rate_currency')
                    ->label('Currency')
                    ->options([
                        (string) config('shop.display_currency') => (string) config('shop.display_currency'),
                        (string) config('shop.list_currency') => (string) config('shop.list_currency'),
                    ])
                    ->default((string) config('shop.display_currency'))
                    ->live()
                    ->required()
                    ->dehydrated(false),
                MoneyInput::make('rate', fn (Get $get) => (string) ($get('rate_currency') ?: config('shop.display_currency')))
                    ->label('Wage')
                    ->required(),
                Select::make('period')
                    ->options(WagePeriod::class)
                    ->default(WagePeriod::Monthly)
                    ->required(),
                Select::make('location_id')
                    ->label('Store or base')
                    ->relationship('location', 'name'),
                DatePicker::make('starts_on')->native(false)->default(today())->required(),
                DatePicker::make('ends_on')
                    ->native(false)
                    ->afterOrEqual('starts_on')
                    ->helperText('Leave empty while the wage is still being paid.'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('rate')
                    ->label('Wage')
                    ->formatStateUsing(fn (StaffWage $record) => $record->rate->formatToLocale((string) config('shop.display_locale')).' '.mb_strtolower($record->period->getLabel())),
                TextColumn::make('location.name')->label('Store'),
                TextColumn::make('starts_on')->date('j M Y')->sortable(),
                TextColumn::make('ends_on')->date('j M Y')->placeholder('Ongoing'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStaffWages::route('/'),
        ];
    }
}
