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
            'address' => $data['user_address'] ?? null,
            'front_image_card' => $this->handleFileUpload($data['front_image_card'] ?? null),
            'back_image_card' => $this->handleFileUpload($data['back_image_card'] ?? null),
            'id_card_selfie_path' => $this->handleFileUpload($data['id_card_selfie_path'] ?? null),
            'role' => RoleUser::CUSTOMER->value,
            'password' => Hash::make('123456'),
        ]);

        if (!empty($data['bank_id']) && !empty($data['account_number']) && !empty($data['account_name'])) {
            UserBankAccount::create([
                'user_id' => $user->id,
                'bank_id' => $data['bank_id'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'],
                'is_verified' => false,
            ]);
        }

        return $user;
    }

    private function handleFileUpload($file): ?string
    {
        if (empty($file)) {
            return null;
        }

        // Nếu là array (multiple files), lấy file đầu tiên
        if (is_array($file)) {
            $file = $file[0] ?? null;
        }

        // Nếu là string (đã có đường dẫn), trả về nguyên vẹn
        if (is_string($file)) {
            return $file;
        }

        // Nếu là UploadedFile, lưu và trả về đường dẫn
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
