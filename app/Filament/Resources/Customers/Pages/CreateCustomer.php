<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
// Redirects to the customer list page after creating a new customer
     protected function getRedirectUrl(): string

    {       
         return $this->getResource()::getUrl('index');
    }

}



   
