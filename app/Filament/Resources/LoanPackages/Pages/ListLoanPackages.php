<?php

namespace App\Filament\Resources\LoanPackages\Pages;

use App\Filament\Resources\LoanPackages\LoanPackagesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanPackages extends ListRecords
{
    protected static string $resource = LoanPackagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
