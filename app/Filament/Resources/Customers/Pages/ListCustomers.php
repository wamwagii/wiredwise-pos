<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Imports\CustomerImporter;
use App\Filament\Exports\CustomerExporter;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
            ImportAction::make()
                ->importer(CustomerImporter::class)
                ->label('Import Customers')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->maxRows(50000)
                ->chunkSize(250),
            
            ExportAction::make()
                ->exporter(CustomerExporter::class)
                ->label('Export Customers')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}