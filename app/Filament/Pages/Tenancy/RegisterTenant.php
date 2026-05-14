<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Tenant;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Tenancy\RegisterTenant as BaseRegisterTenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Add this import

class RegisterTenant extends BaseRegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register your business';
    }
    
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Business Name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('domain', Str::slug($state));
                }),
            TextInput::make('domain')
                ->label('Domain / Subdomain')
                ->required()
                ->maxLength(255)
                ->unique(Tenant::class)
                ->helperText('URL identifier for your business'),
            Select::make('type')
                ->label('Business Type')
                ->options([
                    'bar' => 'Bar',
                    'restaurant' => 'Restaurant',
                    'chemist' => 'Chemist / Pharmacy',
                    'supermarket' => 'Supermarket',
                ])
                ->required(),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->hidden(),
        ];
    }
    
    protected function handleRegistration(array $data): Tenant
    {
        $tenant = Tenant::create($data);
        
        // Option 1: Use Auth facade directly (more explicit)
        $tenant->users()->attach(Auth::id(), ['role' => 'owner']);
        
        // Option 2: Get user first then attach (more verbose but IDE friendly)
        // $user = Auth::user();
        // $tenant->users()->attach($user->id, ['role' => 'owner']);
        
        return $tenant;
    }
}