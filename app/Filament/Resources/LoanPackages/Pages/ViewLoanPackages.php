<?php

namespace App\Filament\Resources\LoanPackages\Pages;

use App\Filament\Resources\LoanPackages\LoanPackagesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLoanPackages extends ViewRecord
{
    protected static string $resource = LoanPackagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
