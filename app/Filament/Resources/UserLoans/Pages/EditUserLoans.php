<?php

namespace App\Filament\Resources\UserLoans\Pages;

use App\Filament\Resources\UserLoans\UserLoansResource;
use App\Services\UserLoanApprovalService;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserLoanLog;
use App\Services\LoanCalculationService;
use App\Services\PaymentService;
use App\Services\UserLoanLogService;
use App\Traits\UserLoanFormLogic;
use App\Utils\Helper;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\PaymentDirection;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

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
            Action::make('approveLoan')
                ->label('Duyệt khoản vay')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === LoanStatus::PENDING->value)
                ->form([
                    Select::make('mode')
                        ->label('Chế độ')
                        ->live()
                        ->options([
                            'approve_only' => 'Chỉ duyệt (chưa giải ngân)',
                            'approve_and_disburse' => 'Duyệt và giải ngân',
                        ])
                        ->default('approve_only')
                        ->required(),
                    DatePicker::make('start_date')
                        ->label('Ngày bắt đầu vay')
                        ->default(now())
                        ->visible(fn ($get) => $get('mode') === 'approve_and_disburse'),
                    TextInput::make('disbursed_amount')
                        ->label('Số tiền giải ngân (VNĐ)')
                        ->numeric()
                        ->default(fn () => $this->getRecord()->principal_amount)
                        ->minValue(0)
                        ->maxValue(fn () => $this->getRecord()->principal_amount)
                        ->suffix('VNĐ')
                        ->visible(fn ($get) => $get('mode') === 'approve_and_disburse')
                        ->required(fn ($get) => $get('mode') === 'approve_and_disburse'),
                    Placeholder::make('payment_qr')
                        ->label('QR thanh toán')
                        ->visible(fn ($get) => $get('mode') === 'approve_and_disburse')
                        ->content(function ($get) {
                            $record = $this->getRecord();
                            $bankAccount = UserBankAccount::query()
                                ->where('user_id', $record->user_id)
                                ->first();

                            if (!$bankAccount || empty($bankAccount->bank_name) || empty($bankAccount->account_number) || empty($bankAccount->account_name)) {
                                return new HtmlString('<p class="text-sm text-gray-500">Người dùng chưa có đủ thông tin tài khoản ngân hàng để tạo QR.</p>');
                            }

                            $amount = (int) ($get('disbursed_amount') ?: $record->principal_amount);
                            $bankCode = self::resolveBankQrCode($bankAccount->bank_name);
                            $description = 'GIAI NGAN ' . $record->id;
                            $accountName = mb_strtoupper($bankAccount->account_name);
                            $qrUrl = Helper::generateQRCodeBanking(
                                $bankCode,
                                $bankAccount->account_number,
                                $accountName,
                                $amount,
                                $description,
                                'compact2',
                            );

                            return new HtmlString(
                                '
                                <div class="space-y-3 rounded-lg border border-gray-200 p-4">'
                                . '<img src="' . e($qrUrl) . '" alt="QR thanh toán" class="mx-auto h-56 w-56 object-contain" />'
                                . '<div class="space-y-1 text-sm">'
                                . '<p><strong>Ngân hàng:</strong> ' . e($bankAccount->bank_name) . '</p>'
                                . '<p><strong>Số tài khoản:</strong> ' . e($bankAccount->account_number) . '</p>'
                                . '<p><strong>Tên tài khoản:</strong> ' . e($bankAccount->account_name) . '</p>'
                                . '<p><strong>Số tiền:</strong> ' . number_format($amount) . ' VNĐ</p>'
                                . '</div>'
                                . '</div>'
                            );
                        }),
                ])
                ->action(function (array $data): void {
                    try {
                        app(UserLoanApprovalService::class)->approve(
                            $this->getRecord(),
                            $data['mode'] ?? 'approve_only',
                            $data['start_date'] ?? null,
                            isset($data['disbursed_amount']) ? (float) $data['disbursed_amount'] : null,
                        );

                        Notification::make()
                            ->title('Duyệt khoản vay thành công')
                            ->success()
                            ->send();

                        $this->refreshFormData([
                            'status',
                            'start_date',
                            'disbursed_amount',
                            'reject_reason',
                            'total_paid_amount',
                        ]);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Duyệt khoản vay thất bại')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('rejectLoan')
                ->label('Từ chối khoản vay')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->getRecord()->status === LoanStatus::PENDING->value)
                ->form([
                    Textarea::make('reject_reason')
                        ->label('Lý do từ chối')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    try {
                        app(UserLoanApprovalService::class)->reject(
                            $this->getRecord(),
                            $data['reject_reason'],
                        );

                        Notification::make()
                            ->title('Từ chối khoản vay thành công')
                            ->success()
                            ->send();

                        $this->refreshFormData([
                            'status',
                            'reject_reason',
                        ]);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Từ chối khoản vay thất bại')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
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

    private static function resolveBankQrCode(?string $bankName): string
    {
        $normalized = mb_strtolower((string) $bankName);

        return match (true) {
            str_contains($normalized, 'vietcombank'),
            str_contains($normalized, 'ngoai thuong') => 'vcb',
            str_contains($normalized, 'techcombank') => 'tcb',
            str_contains($normalized, 'mb'),
            str_contains($normalized, 'quan doi') => 'mbb',
            str_contains($normalized, 'bidv') => 'bidv',
            str_contains($normalized, 'agribank') => 'vba',
            str_contains($normalized, 'vietinbank'),
            str_contains($normalized, 'cong thuong') => 'icb',
            default => 'vcb',
        };
    }
}
