<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Models\Payment;
use App\Models\UserBankAccount;
use App\Services\LoanCalculationService;
use App\Services\PaymentService;
use App\Services\UserLoanLogService;
use App\Traits\UserLoanFormLogic;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\PaymentDirection;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUserLoans extends EditRecord
{
    use UserLoanFormLogic;

    protected LoanCalculationService $loanCalculationService;
    protected PaymentService $paymentService;
    protected UserLoanLogService $userLoanLogService;

    public function boot(LoanCalculationService $loanCalculationService, PaymentService $paymentService, UserLoanLogService $userLoanLogService): void
    {
        $this->loanCalculationService = $loanCalculationService;
        $this->paymentService = $paymentService;
        $this->userLoanLogService = $userLoanLogService;
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

        if (!empty($data['user_id'])) {
            $bankAccount = UserBankAccount::where('user_id', $data['user_id'])->first();
            if ($bankAccount) {
                $data['bank_id'] = $bankAccount->bank_id;
                $data['account_number'] = $bankAccount->account_number;
                $data['account_name'] = $bankAccount->account_name;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->fillAllRelatedInfo($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record->status === LoanStatus::ACTIVE->value && $record->start_date) {
            $result = $this->userLoanLogService->generateLogsForLoan($record);
        }

        if ($record->status === LoanStatus::ACTIVE->value && $record->disbursed_amount > 0) {
            $existingPayment = Payment::where('user_loan_id', $record->id)
                ->where('direction', PaymentDirection::OUT->value)
                ->first();

            if ($existingPayment) {
                $newAmount = $record->disbursed_amount;
                
                $existingPayment->update([
                    'amount' => $newAmount,
                    'description' => "Giải ngân khoản vay #{$record->id} - " . number_format($newAmount) . " VNĐ"
                ]);
                
            } else {
                $disbursedAmount = $record->disbursed_amount;
                
                try {
                    $this->paymentService->createDisbursementPayment(
                        $record,
                        $disbursedAmount,
                        "Giải ngân khoản vay #{$record->id} - " . number_format($disbursedAmount) . " VNĐ"
                    );
                    
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }
    }
}
