<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Details')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Product::uniqueSlug((string) $state)) : null)
                            ->helperText('The model customers search for, e.g. "iPhone 13 128GB". Condition and price belong to each stock unit.'),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        Select::make('category_id')
                            ->relationship('category', 'name', fn (Builder $query) => $query->orderBy('sort_order'))
                            ->required()
                            ->preload(),
                        TextInput::make('brand')->maxLength(255),
                        TextInput::make('model')->maxLength(255),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        KeyValue::make('specs')
                            ->keyLabel('Spec')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
                Section::make('Visibility')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Shown in the shop when at least one unit is in store.')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Featured on the home page'),
                    ]),
                Section::make('Photos')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxSize(8192)
                            ->maxFiles(12)
                            ->helperText('The first photo is the main image. JPEG, PNG or WebP, up to 8 MB each.'),
                    ]),
            ]);
    }
}
