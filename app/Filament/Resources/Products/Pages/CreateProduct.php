<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    
    // Optional: Set default values based on tenant
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure tenant_id is set from current tenant
        if ($tenant = Filament::getTenant()) {
            $data['tenant_id'] = $tenant->id;
        }
        
        // Auto-generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(substr($data['name'], 0, 3)) . rand(1000, 9999);
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}