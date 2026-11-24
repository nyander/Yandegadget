<?php

namespace App\Filament\Resources\Users;

use App\Enums\Role as RoleEnum;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Closure;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Role $record) => RoleEnum::tryFrom($record->name)?->label() ?? $record->name)
                            ->multiple()
                            ->preload()
                            ->required()
                            ->rule(fn (?User $record) => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                                $adminRoleId = Role::findByName(RoleEnum::Admin->value, 'web')->id;

                                if ($record?->is(auth()->user()) && ! in_array($adminRoleId, array_map('intval', (array) $value), true)) {
                                    $fail('You can\'t remove your own admin role.');
                                }
                            })
                            ->helperText('Staff check in shipments and sell. Suppliers offer stock. Customers make requests. Admins run everything.'),
                        Select::make('location_id')
                            ->label('Works at')
                            ->relationship('location', 'name')
                            ->helperText('For staff. Leave empty to give access to every store.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['roles', 'location', 'supplier']))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()
                    ->description(fn (User $record) => $record->isClosed() ? 'Closed: personal details removed' : $record->supplier?->name),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->label('Roles')->badge()
                    ->formatStateUsing(fn (string $state) => RoleEnum::tryFrom($state)?->label() ?? $state),
                TextColumn::make('location.name')->label('Works at')->placeholder('—'),
                IconColumn::make('email_verified_at')->label('Verified')->boolean()
                    ->state(fn (User $record) => $record->email_verified_at !== null),
                TextColumn::make('created_at')->label('Joined')->date()->sortable(),
            ])
            ->filters([
                // Closed accounts stay as anonymous rows (their sales and deposits point to them), out of the way.
                TernaryFilter::make('closed')
                    ->label('Closed accounts')
                    ->placeholder('Show all')
                    ->trueLabel('Only closed accounts')
                    ->falseLabel('Hide closed accounts')
                    ->default(false)
                    ->queries(
                        true: fn (Builder $query) => $query->where('email', 'like', '%'.User::CLOSED_EMAIL_DOMAIN),
                        false: fn (Builder $query) => $query->where('email', 'not like', '%'.User::CLOSED_EMAIL_DOMAIN),
                        blank: fn (Builder $query) => $query,
                    ),
                SelectFilter::make('roles')->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record) => RoleEnum::tryFrom($record->name)?->label() ?? $record->name),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
