<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Filament\Facades\Filament;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('tenant_id')
                    ->default(fn() => Filament::getTenant()?->id),
                    
                Hidden::make('user_id')
                    ->default(fn() => optional(Filament::getTenant())->id),
                    
                Section::make('Invoice Information')
                    ->schema([
                        // Invoice Number - Auto-generated, never editable
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled() // Always disabled
                            ->dehydrated() // Still save the value
                            ->helperText('Auto-generated and cannot be changed'),
                            
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                            
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'mobile_money' => 'Mobile Money',
                                'credit' => 'Credit',
                            ])
                            ->required(),
                            
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'partially_paid' => 'Partially Paid',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                            
                        DateTimePicker::make('paid_at')
                            ->label('Paid Date')
                            ->nullable()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn($get) => $get('payment_status') === 'paid'),
                    ])->columns(2),
                    
                Section::make('Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->selling_price);
                                            $quantity = $get('quantity') ?: 1;
                                            $set('total_price', $product->selling_price * $quantity);
                                            self::recalculateTotals($get, $set);
                                        }
                                    }),
                                    
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $price = $get('unit_price') ?: 0;
                                        $set('total_price', $price * $state);
                                        self::recalculateTotals($get, $set);
                                    }),
                                    
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $quantity = $get('quantity') ?: 1;
                                        $set('total_price', $state * $quantity);
                                        self::recalculateTotals($get, $set);
                                    }),
                                    
                                TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->columnSpanFull()
                            ->cloneable()
                            ->reorderable(),
                    ]),
                    
                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                            
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                self::recalculateTotals($get, $set);
                            }),
                            
                        TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                self::recalculateTotals($get, $set);
                            }),
                            
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(4),
                    
                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
    
    // Helper method to recalculate totals
    protected static function recalculateTotals(callable $get, callable $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        
        foreach ($items as $item) {
            $subtotal += $item['total_price'] ?? 0;
        }
        
        $set('subtotal', $subtotal);
        
        $tax = $get('tax_amount') ?? 0;
        $discount = $get('discount_amount') ?? 0;
        $total = $subtotal + $tax - $discount;
        
        $set('total_amount', max(0, $total));
    }
}