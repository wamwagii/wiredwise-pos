<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Imports\ProductImporter;
use App\Filament\Exports\ProductExporter;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
            ImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Import Products')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->maxRows(50000)
                ->chunkSize(250),
            
            ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label('Export Products')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}