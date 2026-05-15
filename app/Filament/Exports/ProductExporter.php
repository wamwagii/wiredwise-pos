<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('tenant_id')
                ->label('Tenant ID'),
            ExportColumn::make('name')
                ->label('Product Name'),
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('barcode')
                ->label('Barcode'),
            ExportColumn::make('description')
                ->label('Description'),
            ExportColumn::make('purchase_price')
                ->label('Purchase Price')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            ExportColumn::make('selling_price')
                ->label('Selling Price')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            ExportColumn::make('stock_quantity')
                ->label('Stock Quantity'),
            ExportColumn::make('min_stock_threshold')
                ->label('Min Stock Threshold'),
            ExportColumn::make('unit')
                ->label('Unit'),
            ExportColumn::make('category')
                ->label('Category'),
            ExportColumn::make('sub_category')
                ->label('Sub Category'),
            ExportColumn::make('tax_info')
                ->label('Tax Info'),
            ExportColumn::make('is_active')
                ->label('Active')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s')),
            ExportColumn::make('updated_at')
                ->label('Updated At')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s')),
            ExportColumn::make('deleted_at')
                ->label('Deleted At')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        $body .= ' Click the notification to download your file.';

        return $body;
    }
}