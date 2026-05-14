<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Enter the basic details for this tenant')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tenant Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter business name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // Auto-generate domain from name if domain is empty
                                if (empty($get('domain'))) {
                                    $domain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $state));
                                    $set('domain', $domain);
                                }
                            }),
                        
                        TextInput::make('domain')
                            ->label('Domain / Subdomain')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique identifier for this tenant (e.g., "sunsetbar")')
                            ->prefix('https://')
                            ->suffix('.yourdomain.com'),
                        
                        Select::make('type')
                            ->label('Business Type')
                            ->options([
                                'bar' => 'Bar',
                                'restaurant' => 'Restaurant',
                                'chemist' => 'Chemist / Pharmacy',
                                'supermarket' => 'Supermarket',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->helperText('Select the type of business for this tenant'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->required()
                            ->default(true)
                            ->helperText('Inactive tenants cannot access the system')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])->columns(2),
                
                Section::make('Additional Settings')
                    ->description('Configure custom settings for this tenant')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Tenant Settings')
                            ->helperText('Add custom configuration key-value pairs')
                            ->keyLabel('Setting Name')
                            ->valueLabel('Setting Value')
                            ->default([
                                'currency' => 'USD',
                                'timezone' => 'UTC',
                                'date_format' => 'Y-m-d',
                                'receipt_footer' => 'Thank you for your business!',
                            ])
                            ->reorderable()
                            ->addActionLabel('Add Setting'),
                    ]),
            ]);
    }
}