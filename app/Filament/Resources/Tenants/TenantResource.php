<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Resources\Tenants\Pages\EditTenant;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\Tenants\Pages\ViewTenant;
use App\Filament\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Resources\Tenants\Schemas\TenantInfolist;
use App\Filament\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $recordTitleAttribute = 'name';
    
    // CRITICAL: Disable tenant scoping for this resource since it IS the tenant model
    protected static bool $isScopedToTenant = false;

    // Only show Tenant resource in navigation for super admin
    public static function shouldRegisterNavigation(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->email === 'dev.wiredwise@outlook.com';
    }

    // Scope the query - super admin sees all, others see only their tenants
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        /** @var User|null $user */
        $user = Auth::user();
        
        // Super admin can see all tenants
        if ($user && $user->email === 'dev.wiredwise@outlook.com') {
            return $query;
        }
        
        // Regular users only see tenants they belong to
        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user?->id);
        });
    }

    // Prevent regular users from accessing tenant records directly
    public static function getRecordRouteBinding($key): ?Tenant
    {
        /** @var User|null $user */
        $user = Auth::user();
        $query = static::getEloquentQuery();
        
        // If not super admin, ensure user has access to this tenant
        if ($user && $user->email !== 'dev.wiredwise@outlook.com') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        return $query->find($key);
    }

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // Navigation badge - only show to super admin
    public static function getNavigationBadge(): ?string
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if ($user && $user->email === 'dev.wiredwise@outlook.com') {
            return (string) Tenant::count();
        }
        return null;
    }
    
    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'view' => ViewTenant::route('/{record}'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}