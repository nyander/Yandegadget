<?php

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Models\Payment;
use BackedEnum;
use Brick\Money\Money;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Online payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'payable']))
            ->columns([
                TextColumn::make('reference')->searchable()->copyable()->fontFamily('mono')->size('sm'),
                TextColumn::make('status')->badge(),
                TextColumn::make('money')->label('Amount')
                    ->formatStateUsing(fn (Money $state) => $state->formatToLocale('en_GH'))
                    ->sortable(['amount']),
                TextColumn::make('payable.reference')->label('For'),
                TextColumn::make('customer.name')->label('Customer')->searchable(),
                TextColumn::make('channel')->placeholder('—'),
                TextColumn::make('provider')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('refund_status')->label('Refund')->placeholder('—'),
                TextColumn::make('paid_at')->label('Paid')->dateTime()->sortable()->placeholder('—'),
                TextColumn::make('created_at')->label('Started')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(PaymentStatus::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
        ];
    }
}
