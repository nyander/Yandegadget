<?php

namespace App\Filament\Resources\StockUnits\Actions;

use App\Enums\StockStatus;
use App\Filament\Support\MoneyInput;
use App\Models\Condition;
use App\Models\Location;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockIntake;
use Database\Seeders\LocationSeeder;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * "Add several": record a batch of the same product bought together, for
 * example 10 iPhone 13s in grade B at £250 each. Each becomes its own item.
 */
class AddSeveralUnitsAction
{
    /**
     * @param  (callable(): ?Product)|null  $product  The product, when the action sits on a product page.
     */
    public static function make(?callable $product = null): Action
    {
        $listCurrency = (string) config('shop.list_currency');
        $ukBase = fn () => Location::where('code', 'UK-BUYING')->value('id');
        $direct = fn () => Location::where('code', LocationSeeder::DIRECT)->value('id');
        $store = fn () => Location::where('code', 'ACC-LEGON')->value('id');

        return Action::make('addSeveral')
            ->label('Add several')
            ->icon(Heroicon::OutlinedSquare3Stack3d)
            ->color('gray')
            ->modalHeading('Add several items')
            ->modalDescription('For a batch of the same thing bought together. Each one still gets its own reference, so you can sell, ship and track them separately.')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel('Add items')
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->hidden($product !== null)
                            ->columnSpanFull(),
                        TextInput::make('quantity')
                            ->label('How many')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(StockIntake::MAX_AT_ONCE)
                            ->default(2)
                            ->required()
                            ->live(debounce: 400),
                        Select::make('condition_id')
                            ->label('Condition')
                            ->options(fn () => Condition::query()->orderBy('sort_order')->get()->mapWithKeys(fn (Condition $c) => [$c->id => "{$c->grade} – {$c->name}"]))
                            ->required(),
                        MoneyInput::withCedis(MoneyInput::make('cost', $listCurrency)->label('Cost of each')->required(), $listCurrency),
                        MoneyInput::withCedis(MoneyInput::make('price', $listCurrency)
                            ->label('List price of each')
                            ->required(), $listCurrency, 'Customers see '),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn () => Supplier::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                        DatePicker::make('purchased_at')->label('Bought on')->default(now())->maxDate(now())->required(),
                        Select::make('status')
                            ->label('Where they are now')
                            ->options([
                                StockStatus::Purchased->value => 'Bought, still in the UK',
                                'direct' => 'Bought, the supplier ships straight to Accra',
                                StockStatus::Received->value => 'Already in the Accra store',
                            ])
                            ->default(StockStatus::Purchased->value)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('location_id', match ($state) {
                                StockStatus::Received->value => $store(),
                                'direct' => $direct(),
                                default => $ukBase(),
                            })),
                        Select::make('location_id')
                            ->label('Location')
                            ->options(fn () => Location::query()->orderBy('name')->pluck('name', 'id'))
                            ->default($ukBase)
                            ->required(),
                        Textarea::make('serials')
                            ->label('Serial / IMEI numbers (optional)')
                            ->rows(4)
                            ->placeholder("One per line, in any order.\n356789104512345\n356789104512346")
                            ->helperText(fn (Get $get) => 'One per line. '.self::serialsSummary($get('serials'), $get('quantity')))
                            ->live(debounce: 500)
                            ->rule(fn (Get $get) => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                if (count(StockIntake::parseSerials($value)) > (int) $get('quantity')) {
                                    $fail('There are more serial numbers than items. Raise "How many" or remove some lines.');
                                }
                            })
                            ->columnSpanFull(),
                        Textarea::make('condition_notes')
                            ->label('Notes for all of them')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ])
            ->action(function (array $data, StockIntake $intake) use ($product): void {
                /** @var User $user */
                $user = auth()->user();
                $target = $product !== null ? $product() : Product::query()->whereKey($data['product_id'] ?? null)->first();

                if ($target === null) {
                    Notification::make()->danger()->title('Pick a product first')->send();

                    return;
                }

                // Bought for direct delivery: still "purchased", waiting to be shipped by the supplier.
                $data['status'] = $data['status'] === 'direct' ? StockStatus::Purchased->value : $data['status'];

                $units = $intake->addSeveral(
                    $target,
                    collect($data)->only(['condition_id', 'cost', 'price', 'supplier_id', 'purchased_at', 'status', 'location_id', 'condition_notes'])->all(),
                    (int) $data['quantity'],
                    StockIntake::parseSerials($data['serials'] ?? null),
                    $user,
                );

                $first = $units[0]->reference;
                $last = $units[count($units) - 1]->reference;

                Notification::make()->success()
                    ->title(count($units).' '.str('item')->plural(count($units)).' added')
                    ->body($first === $last ? $first : "{$first} to {$last}. Add photos to any item with marks by opening it.")
                    ->send();
            });
    }

    private static function serialsSummary(mixed $text, mixed $quantity): string
    {
        $count = count(StockIntake::parseSerials(is_string($text) ? $text : null));
        $quantity = (int) $quantity;

        return match (true) {
            $count === 0 => 'Leave empty if they have none.',
            $count < $quantity => "{$count} of {$quantity} entered; the rest are left blank.",
            $count === $quantity => "{$count} of {$quantity} entered.",
            default => "{$count} entered for {$quantity} items: too many.",
        };
    }
}
