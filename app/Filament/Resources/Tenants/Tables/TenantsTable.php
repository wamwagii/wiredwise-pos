<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('domain')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Domain copied!'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bar' => 'warning',
                        'restaurant' => 'success',
                        'chemist' => 'info',
                        'supermarket' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Deleted At'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'bar' => 'Bar',
                        'restaurant' => 'Restaurant',
                        'chemist' => 'Chemist',
                        'supermarket' => 'Supermarket',
                    ])
                    ->label('Tenant Type'),
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean(),
                // Temporarily comment out TrashedFilter
                // TrashedFilter::make()
                //     ->label('Deleted Records'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    // RestoreBulkAction::make(),
                ]),
            ]);
    }
}