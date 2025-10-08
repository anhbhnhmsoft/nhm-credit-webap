<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Services\LoanCalculationService;
use App\Services\PaymentService;
use App\Traits\UserLoanFormLogic;
use App\Utils\Constants\LoanStatus;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateUserLoans extends CreateRecord
{
    use UserLoanFormLogic;

    protected LoanCalculationService $loanCalculationService;
    protected PaymentService $paymentService;

    public function boot(LoanCalculationService $loanCalculationService, PaymentService $paymentService): void
    {
        $this->loanCalculationService = $loanCalculationService;
        $this->paymentService = $paymentService;
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
        $data = $this->loanCalculationService->calculateLoanData($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        if ($record->status === LoanStatus::ACTIVE->value && $record->disbursed_amount > 0) {
            $disbursedAmount = $record->disbursed_amount;

            $record->update(['disbursed_amount' => 0]);

            $this->paymentService->createDisbursementPayment(
                $record,
                $disbursedAmount,
                "Giải ngân khoản vay #{$record->id} - " . number_format($disbursedAmount) . " VNĐ"
            );
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
