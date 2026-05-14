<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter customer name'),
                            
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+1 234 567 8900'),
                            
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('customer@example.com'),
                    ])->columns(2),
                    
                Section::make('Loyalty Program')
                    ->schema([
                        TextInput::make('loyalty_card_number')
                            ->label('Loyalty Card Number')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique card number for loyalty tracking'),
                            
                        TextInput::make('loyalty_points')
                            ->label('Loyalty Points')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01),
                    ])->columns(2),
                    
                Section::make('Address Information')
                    ->schema([
                        Textarea::make('address')
                            ->rows(3)
                            ->placeholder('Street address, city, state, zip code'),
                    ]),
                    
                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Customer')
                            ->default(true)
                            ->helperText('Inactive customers cannot make purchases'),
                    ]),
            ]);
    }
}