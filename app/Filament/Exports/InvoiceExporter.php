<?php

namespace App\Filament\Exports;

use App\Models\Invoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class InvoiceExporter extends Exporter
{
    protected static ?string $model = Invoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('invoice_number')
                ->label('Invoice Number')
                ->primary(),
            
            ExportColumn::make('customer.name')
                ->label('Customer Name'),
            
            ExportColumn::make('customer.email')
                ->label('Customer Email'),
            
            ExportColumn::make('user.name')
                ->label('Cashier'),
            
            ExportColumn::make('subtotal')
                ->label('Subtotal')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            
            ExportColumn::make('tax_amount')
                ->label('Tax Amount')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            
            ExportColumn::make('discount_amount')
                ->label('Discount Amount')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            
            ExportColumn::make('total_amount')
                ->label('Total Amount')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            
            ExportColumn::make('payment_method')
                ->label('Payment Method'),
            
            ExportColumn::make('payment_status')
                ->label('Payment Status')
                ->badge(),
            
            ExportColumn::make('paid_at')
                ->label('Paid Date')
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : ''),
            
            ExportColumn::make('notes')
                ->label('Notes'),
            
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($state) => $state->format('Y-m-d H:i:s')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your invoice export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        $body .= ' Click the notification to download your file.';

        return $body;
    }
}