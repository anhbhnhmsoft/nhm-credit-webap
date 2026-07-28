<?php

namespace App\Filament\Resources\UserLoanLogs\Pages;

use App\Filament\Resources\UserLoanLogs\UserLoanLogsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUserLoanLogs extends EditRecord
{
    protected static string $resource = UserLoanLogsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Lịch trả nợ',
            '' => 'Sửa lịch trả nợ',
        ];
    }
}
