<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';
    
    // This resource IS scoped to tenant
    protected static bool $isScopedToTenant = true;
    
    // The ownership relationship on the Customer model
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';

    // Scope queries to only show customers for the current tenant
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // If there's a current tenant, scope the query
        if ($tenant = Filament::getTenant()) {
            $query->where('tenant_id', $tenant->id);
        }
        
        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    // Navigation badge showing customer count
    public static function getNavigationBadge(): ?string
    {
        if ($tenant = Filament::getTenant()) {
            return (string) Customer::where('tenant_id', $tenant->id)->count();
        }
        return null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
    
    // Remove or simplify this method - it's not needed
    // public static function getRecordRouteBindingEloquentQuery(): Builder
    // {
    //     return parent::getRecordRouteBindingEloquentQuery()
    //         ->withoutGlobalScopes([
    //             // SoftDeletingScope::class,
    //         ]);
    // }
}