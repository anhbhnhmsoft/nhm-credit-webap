<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Services\LoanCalculationService;
use App\Traits\UserLoanFormLogic;
use Filament\Resources\Pages\CreateRecord;

class CreateUserLoans extends CreateRecord
{
    use UserLoanFormLogic;

    protected LoanCalculationService $loanCalculationService;

    public function boot(LoanCalculationService $loanCalculationService): void
    {
        $this->loanCalculationService = $loanCalculationService;
    }

    protected static string $resource = UserLoansResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Khoản vay',
            '' => 'Tạo khoản vay',
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->fillAllRelatedInfo($data);
        $data = $this->loanCalculationService->fillTotalAmount($data);
        
        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
