<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Exceptions\ServiceException;
use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Services\LoanCalculationService;
use App\Services\PaymentService;
use App\Services\UserLoanLogService;
use App\Traits\UserLoanFormLogic;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\RoleUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateUserLoans extends CreateRecord
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

        if (empty($data['user_id']) && !empty($data['user_name'])) {
            try {
                $user = $this->createNewUser($data);
                $data['user_id'] = $user->id;
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } elseif (!empty($data['user_id'])) {
            $this->updateExistingUser($data);
        }

        return $data;
    }

    private function createNewUser(array $data): User
    {
        if (!empty($data['user_email'])) {
            $existingUser = User::where('email', $data['user_email'])->first();
            if ($existingUser) {
                throw new ServiceException("Email '{$data['user_email']}' đã được sử dụng bởi người dùng khác.");
            }
        }

        if (!empty($data['user_phone'])) {
            $existingUser = User::where('phone', $data['user_phone'])->first();
            if ($existingUser) {
                throw new ServiceException("Số điện thoại '{$data['user_phone']}' đã được sử dụng bởi người dùng khác.");
            }
        }

        $user = User::create([
            'name' => $data['user_name'],
            'phone' => $data['user_phone'] ?? null,
            'email' => $data['user_email'] ?? null,
            'number_card' => $data['user_number_card'] ?? null,
            'front_image_card' => $this->handleFileUpload($data['front_image_card'] ?? null),
            'back_image_card' => $this->handleFileUpload($data['back_image_card'] ?? null),
            'id_card_selfie_path' => $this->handleFileUpload($data['id_card_selfie_path'] ?? null),
            'role' => RoleUser::CUSTOMER->value,
            'password' => Hash::make($data['user_phone']),
        ]);

        if ((!empty($data['bank_id']) || !empty($data['bank_name'])) && !empty($data['account_number']) && !empty($data['account_name'])) {
            UserBankAccount::create([
                'user_id' => $user->id,
                'bank_id' => $data['bank_id'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'],
                'is_verified' => false,
            ]);
        }

        return $user;
    }

    private function updateExistingUser(array $data): void
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $updateData = [
                'name' => $data['user_name'] ?? $user->name,
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

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        if ($record->status === LoanStatus::ACTIVE->value && $record->start_date) {
            $this->userLoanLogService->generateLogsForLoan($record);
        }

        if ($record->status === LoanStatus::ACTIVE->value && $record->disbursed_amount > 0) {
            $disbursedAmount = $record->disbursed_amount;

            $this->paymentService->createDisbursementPayment(
                $record,
                $disbursedAmount,
                "Giải ngân khoản vay #{$record->id} - " . number_format($disbursedAmount) . " VNĐ"
            );
        }

        $this->updateTotalPaidAmount($record);
    }

    private function updateTotalPaidAmount($record): void
    {
        $totalPaidFromLogs = \App\Models\UserLoanLog::where('user_loan_id', $record->id)
            ->sum('total_paid');

        $record->update(['total_paid_amount' => $totalPaidFromLogs]);
    }


    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
