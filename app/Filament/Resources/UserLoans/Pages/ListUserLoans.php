<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserLoans extends ListRecords
{
    protected static string $resource = UserLoansResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Khoản vay',
            '' => 'Danh sách các khoản vay',
        ];
    }
}
