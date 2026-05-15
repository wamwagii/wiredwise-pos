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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('tenant_id')
                    ->default(fn() => optional(Filament::getTenant())->id),
                    
                Hidden::make('user_id')
                    ->default(fn() => auth()->id()),
                    
                Section::make('Invoice Information')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated()
                            ->default('INV-' . strtoupper(uniqid()))
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
                            ->required()
                            ->default('cash'),
                            
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'partially_paid' => 'Partially Paid',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('pending')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Fix: Use $get to get values, $set to set values
                                $paymentStatus = $get('payment_status');
                                if ($paymentStatus === 'paid') {
                                    $paidAt = $get('paid_at');
                                    if (empty($paidAt)) {
                                        $set('paid_at', now()->format('Y-m-d H:i:s'));
                                    }
                                }
                            }),
                            
                        DateTimePicker::make('paid_at')
                            ->label('Paid Date')
                            ->nullable()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn(Get $get) => $get('payment_status') === 'paid'),
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
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->selling_price);
                                            $quantity = $get('quantity') ?: 1;
                                            $set('total_price', $product->selling_price * $quantity);
                                        }
                                        // Recalculate totals when product changes
                                        self::recalculateTotals($get, $set);
                                    }),
                                    
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = $get('unit_price') ?: 0;
                                        $set('total_price', $price * $state);
                                        // Recalculate totals when quantity changes
                                        self::recalculateTotals($get, $set);
                                    }),
                                    
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $quantity = $get('quantity') ?: 1;
                                        $set('total_price', $state * $quantity);
                                        // Recalculate totals when price changes
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
                            ->reorderable()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Recalculate totals when items are added/removed
                                self::recalculateTotals($get, $set);
                            }),
                    ]),
                    
                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateTotals($get, $set);
                            }),
                            
                        TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateTotals($get, $set);
                            }),
                            
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
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
    
    // Helper method to recalculate subtotal and total
    protected static function recalculateTotals(Get $get, Set $set): void
    {
        // Get all items from the repeater
        $items = $get('items') ?? [];
        
        // Calculate subtotal by summing all item totals
        $subtotal = 0;
        foreach ($items as $item) {
            if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                $subtotal += (float) $item['total_price'];
            }
        }
        
        // Update subtotal field
        $set('subtotal', round($subtotal, 2));
        
        // Get tax and discount
        $tax = (float) $get('tax_amount') ?? 0;
        $discount = (float) $get('discount_amount') ?? 0;
        
        // Calculate total
        $total = $subtotal + $tax - $discount;
        
        // Update total amount (ensure it's not negative)
        $set('total_amount', round(max(0, $total), 2));
    }
}