<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Show tenant name instead of ID (helpful for super admins)
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('SKU copied!'),
                    
                TextColumn::make('barcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('category')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'beverages' => 'info',
                        'food' => 'success',
                        'medicine' => 'warning',
                        'grocery' => 'primary',
                        default => 'gray',
                    }),
                    
                TextColumn::make('selling_price')
                    ->money()
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),
                    
                TextColumn::make('purchase_price')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state, $record): string => 
                        $state <= $record->min_stock_threshold ? 'danger' : 'success'
                    )
                    ->weight('bold'),
                    
                TextColumn::make('min_stock_threshold')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Min Stock'),
                    
                TextColumn::make('unit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('sub_category')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
                    
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'beverages' => 'Beverages',
                        'food' => 'Food',
                        'medicine' => 'Medicine',
                        'grocery' => 'Grocery',
                        'electronics' => 'Electronics',
                        'alcohol' => 'Alcohol',
                    ])
                    ->label('Category'),
                    
                SelectFilter::make('unit')
                    ->options([
                        'piece' => 'Piece',
                        'kg' => 'Kilogram',
                        'liter' => 'Liter',
                        'bottle' => 'Bottle',
                        'can' => 'Can',
                        'pack' => 'Pack',
                    ])
                    ->label('Unit'),
                    
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->searchable()
            ->paginated([10, 25, 50, 100]);
    }
}