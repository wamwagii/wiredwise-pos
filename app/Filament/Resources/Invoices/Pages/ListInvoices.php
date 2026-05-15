<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Imports\InvoiceImporter;
use App\Filament\Exports\InvoiceExporter;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
            ImportAction::make()
                ->importer(InvoiceImporter::class)
                ->label('Import Invoices')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->maxRows(50000)
                ->chunkSize(250),
            
            ExportAction::make()
                ->exporter(InvoiceExporter::class)
                ->label('Export Invoices')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}