<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Services\LoanCalculationService;
use App\Traits\UserLoanFormLogic;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUserLoans extends EditRecord
{
    use UserLoanFormLogic;

    protected LoanCalculationService $loanCalculationService;

    public function boot(LoanCalculationService $loanCalculationService): void
    {
        $this->loanCalculationService = $loanCalculationService;
    }

    protected static string $resource = UserLoansResource::class;

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
            url()->previous() => 'Khoản vay',
            '' => 'Sửa khoản vay',
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = $this->fillAllRelatedInfo($data);
        $data = $this->loanCalculationService->fillTotalAmount($data);
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->fillAllRelatedInfo($data);
        $data = $this->loanCalculationService->fillTotalAmount($data);
        
        return $data;
    }
}
