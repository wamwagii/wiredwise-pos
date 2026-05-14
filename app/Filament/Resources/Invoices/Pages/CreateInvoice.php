<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate unique invoice number
        $data['invoice_number'] = $this->generateInvoiceNumber();
        
        // Ensure tenant_id is set
        if ($tenant = Filament::getTenant()) {
            $data['tenant_id'] = $tenant->id;
        }
        
        // Set user_id
         $data['tenant_id'] = $tenant->id;
        
        // Calculate totals if not set
        if (empty($data['total_amount'])) {
            $subtotal = $data['subtotal'] ?? 0;
            $tax = $data['tax_amount'] ?? 0;
            $discount = $data['discount_amount'] ?? 0;
            $data['total_amount'] = $subtotal + $tax - $discount;
        }
        
        return $data;
    }
    
    protected function generateInvoiceNumber(): string
    {
        $tenant = Filament::getTenant();
        $prefix = 'INV';
        
        // Add tenant-specific prefix
        if ($tenant) {
            $prefix = strtoupper(substr($tenant->name, 0, 3));
        }
        
        // Get the last invoice number
        $lastInvoice = \App\Models\Invoice::where('tenant_id', $tenant?->id)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice && preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: PREFIX-YYYYMMDD-XXXX
        return sprintf(
            '%s-%s-%04d',
            $prefix,
            now()->format('Ymd'),
            $nextNumber
        );
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Invoice created successfully!';
    }
}