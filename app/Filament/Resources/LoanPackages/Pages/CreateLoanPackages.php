<?php

namespace App\Filament\Resources\LoanPackages\Pages;

use App\Filament\Resources\LoanPackages\LoanPackagesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoanPackages extends CreateRecord
{
    protected static string $resource = LoanPackagesResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
