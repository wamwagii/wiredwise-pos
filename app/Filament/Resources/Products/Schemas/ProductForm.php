<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // REMOVE this field - it should be set automatically
                // TextInput::make('tenant_id')->required()->numeric(),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('barcode')
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),
                TextInput::make('purchase_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('min_stock_threshold')
                    ->required()
                    ->numeric()
                    ->default(5)
                    ->minValue(0),
                Select::make('unit')
                    ->required()
                    ->options([
                        'piece' => 'Piece',
                        'kg' => 'Kilogram',
                        'liter' => 'Liter',
                        'bottle' => 'Bottle',
                        'can' => 'Can',
                        'pack' => 'Pack',
                        'box' => 'Box',
                    ])
                    ->default('piece'),
                Select::make('category')
                    ->required()
                    ->options([
                        'beverages' => 'Beverages',
                        'food' => 'Food',
                        'medicine' => 'Medicine',
                        'grocery' => 'Grocery',
                        'electronics' => 'Electronics',
                        'alcohol' => 'Alcohol',
                        'soft_drinks' => 'Soft Drinks',
                    ])
                    ->searchable(),
                TextInput::make('sub_category')
                    ->maxLength(255),
                TextInput::make('tax_info')
                    ->label('Tax Rate (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100),
                Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }
}