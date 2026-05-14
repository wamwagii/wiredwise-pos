<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Invoice updated successfully!';
    }
    
    // Add custom actions for status updates
    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_as_paid')
                ->label('Mark as Paid')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $this->record->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    $this->getSavedNotificationTitle();
                    $this->refresh();
                })
                ->visible(fn() => $this->record->payment_status !== 'paid'),
                
            Action::make('mark_as_pending')
                ->label('Mark as Pending')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->action(function () {
                    $this->record->update([
                        'payment_status' => 'pending',
                        'paid_at' => null,
                    ]);
                    $this->refresh();
                })
                ->visible(fn() => $this->record->payment_status !== 'pending'),
                
            Action::make('refund')
                ->label('Refund')
                ->color('danger')
                ->icon('heroicon-o-arrow-uturn-left')
                ->action(function () {
                    $this->record->update([
                        'payment_status' => 'refunded',
                    ]);
                    $this->refresh();
                })
                ->visible(fn() => $this->record->payment_status === 'paid'),
        ];
    }
}