<?php

namespace App\Filament\Resources\BuyingListItems;

use App\Enums\BuyingListStatus;
use App\Filament\Resources\BuyingListItems\Pages\ManageBuyingListItems;
use App\Filament\Support\MoneyInput;
use App\Models\BuyingListItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * What to look for on the next UK buying trip. Items are usually added from
 * the dashboard's "What sold" details, or by hand.
 */
class BuyingListItemResource extends Resource
{
    protected static ?string $model = BuyingListItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Buying';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'buying list item';

    protected static ?string $pluralModelLabel = 'buying list';

    protected static ?string $navigationLabel = 'Buying list';

    protected static ?string $slug = 'buying-list';

    protected static ?string $recordTitleAttribute = 'description';

    public static function getNavigationBadge(): ?string
    {
        $open = BuyingListItem::where('status', BuyingListStatus::ToBuy)->count();

        return $open > 0 ? (string) $open : null;
    }

    /**
     * The fields for one item, shared with the dashboard's "Add to buying list".
     *
     * @return list<Component|Field>
     */
    public static function fields(): array
    {
        return [
            TextInput::make('description')
                ->label('What to buy')
                ->placeholder('e.g. iPhone 13 128GB')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->preload(),
            Select::make('condition_id')
                ->label('Condition')
                ->relationship('condition', 'name')
                ->preload(),
            TextInput::make('quantity')
                ->label('How many')
                ->integer()
                ->minValue(1)
                ->maxValue(999)
                ->default(1)
                ->required(),
            MoneyInput::make('target_cost', 'GBP')
                ->label('Pay up to (each)')
                ->helperText('The most to pay for each one in the UK. From the dashboard, this starts at what you paid on average last time.'),
            Textarea::make('notes')->rows(2)->columnSpanFull(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('description')->label('What to buy')->searchable()->wrap(),
                TextColumn::make('category.name')->label('Category')->placeholder('—'),
                TextColumn::make('condition.name')->label('Condition')->placeholder('—'),
                TextColumn::make('quantity')->label('How many')->alignEnd(),
                TextColumn::make('target_cost')
                    ->label('Pay up to')
                    ->alignEnd()
                    ->formatStateUsing(fn (BuyingListItem $record) => $record->target_cost?->formatToLocale('en_GB'))
                    ->placeholder('—'),
                TextColumn::make('status')->badge(),
                TextColumn::make('source')->label('Why')->color('gray')->wrap()->toggleable(),
                TextColumn::make('created_at')->label('Added')->date('j M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BuyingListStatus::class)
                    ->default(BuyingListStatus::ToBuy->value),
            ])
            ->recordActions([
                Action::make('bought')
                    ->label('Bought')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (BuyingListItem $record) => $record->status === BuyingListStatus::ToBuy)
                    ->authorize(fn (BuyingListItem $record) => auth()->user()?->can('update', $record) ?? false)
                    ->action(fn (BuyingListItem $record) => $record->update(['status' => BuyingListStatus::Bought, 'bought_at' => now()]))
                    ->successNotificationTitle('Marked as bought'),
                Action::make('drop')
                    ->label('Not needed')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('gray')
                    ->visible(fn (BuyingListItem $record) => $record->status === BuyingListStatus::ToBuy)
                    ->authorize(fn (BuyingListItem $record) => auth()->user()?->can('update', $record) ?? false)
                    ->action(fn (BuyingListItem $record) => $record->update(['status' => BuyingListStatus::Dropped])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBuyingListItems::route('/'),
        ];
    }
}
