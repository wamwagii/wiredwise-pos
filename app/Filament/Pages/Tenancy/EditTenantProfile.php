<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Pages\Tenancy\EditTenantProfile as BaseEditTenantProfile;
use Filament\Schemas\Schema;

class EditTenantProfile extends BaseEditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Business profile';
    }
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Business Name')
                    ->required(),
                TextInput::make('domain')
                    ->label('Domain')
                    ->required(),
                Select::make('type')
                    ->label('Business Type')
                    ->options([
                        'bar' => 'Bar',
                        'restaurant' => 'Restaurant',
                        'chemist' => 'Chemist',
                        'supermarket' => 'Supermarket',
                    ])
                    ->required(),
            ]);
    }
}