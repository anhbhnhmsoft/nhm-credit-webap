<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserLoanLog;
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
        // Load total_paid_amount từ UserLoanLog khi edit
        if (isset($data['id'])) {
            $totalPaidFromLogs = \App\Models\UserLoanLog::where('user_loan_id', $data['id'])
                ->sum('total_paid');
            $data['total_paid_amount'] = $totalPaidFromLogs;
        }

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

        if (!empty($data['user_id'])) {
            $this->updateUserInfo($data);
        }

        return $data;
    }

    private function updateUserInfo(array $data): void
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $updateData = [
                'name' => $data['user_name'],
                'phone' => $data['user_phone'] ?? $user->phone,
                'email' => $data['user_email'] ?? $user->email,
                'number_card' => $data['user_number_card'] ?? $user->number_card,
            ];

            if (isset($data['front_image_card'])) {
                $updateData['front_image_card'] = $this->handleFileUpload($data['front_image_card']);
            }
            if (isset($data['back_image_card'])) {
                $updateData['back_image_card'] = $this->handleFileUpload($data['back_image_card']);
            }
            if (isset($data['id_card_selfie_path'])) {
                $updateData['id_card_selfie_path'] = $this->handleFileUpload($data['id_card_selfie_path']);
            }

            $user->update($updateData);

            if ((!empty($data['bank_id']) || !empty($data['bank_name'])) && !empty($data['account_number']) && !empty($data['account_name'])) {
                $existingBankAccount = UserBankAccount::where('user_id', $user->id)->first();
                
                if ($existingBankAccount) {
                    $existingBankAccount->update([
                        'bank_id' => $data['bank_id'] ?? $existingBankAccount->bank_id,
                        'bank_name' => $data['bank_name'] ?? $existingBankAccount->bank_name,
                        'account_number' => $data['account_number'],
                        'account_name' => $data['account_name'],
                    ]);
                } else {
                    UserBankAccount::create([
                        'user_id' => $user->id,
                        'bank_id' => $data['bank_id'] ?? null,
                        'bank_name' => $data['bank_name'] ?? null,
                        'account_number' => $data['account_number'],
                        'account_name' => $data['account_name'],
                        'is_verified' => false,
                    ]);
                }
            }
        }
    }

    private function handleFileUpload($file): ?string
    {
        if (empty($file)) {
            return null;
        }

        if (is_array($file)) {
            $file = $file[0] ?? null;
        }

        if (is_string($file)) {
            return $file;
        }

        if ($file instanceof \Illuminate\Http\UploadedFile) {
            return $file->store('user-documents', 'private');
        }

        return null;
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

        $this->updateTotalPaidAmount($record);
        
        $this->syncUserLoanLogsWithPaidAmount($record);
    }

    private function updateTotalPaidAmount($record): void
    {
        $totalPaidFromLogs = UserLoanLog::where('user_loan_id', $record->id)
            ->sum('total_paid');

        $record->update(['total_paid_amount' => $totalPaidFromLogs]);
    }

    private function syncUserLoanLogsWithPaidAmount($record): void
    {
        $formData = $this->form->getState();
        $totalPaidFromForm = $formData['total_paid_amount'] ?? 0;
        
        if ($totalPaidFromForm > 0) {
            $firstLog = UserLoanLog::where('user_loan_id', $record->id)
                ->orderBy('installment_no')
                ->first();
                
            if ($firstLog) {
                $firstLog->update(['total_paid' => $totalPaidFromForm]);
            }
        }
    }
}
