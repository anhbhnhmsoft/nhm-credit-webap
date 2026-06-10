<?php

namespace App\Filament\Resources\UserLoans\Tables;

use App\Models\UserBankAccount;
use App\Services\UserLoanApprovalService;
use App\Utils\Helper;
use App\Utils\Constants\LoanStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\HtmlString;

class UserLoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),
                TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('Số điện thoại')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.address')
                    ->label('CCCD/CMND')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('principal_amount')
                    ->label('Số tiền vay')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('term_months')
                    ->label('Kỳ hạn')
                    ->alignCenter(),
                TextColumn::make('interest_rate_year')
                    ->label('Phí quá hạn')
                    ->money('VND')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Ngày vay')
                    ->date('d/m/Y')
                    ->alignCenter(),
                TextColumn::make('due_date')
                    ->label('Ngày tất toán')
                    ->date('d/m/Y')
                    ->alignCenter(),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->alignCenter()
                    ->colors([
                        'gray' => LoanStatus::PENDING->value,
                        'info' => LoanStatus::APPROVED->value,
                        'danger' => LoanStatus::REJECTED->value,
                        'primary' => LoanStatus::ACTIVE->value,
                        'success' => LoanStatus::COMPLETED->value,
                    ])
                    ->formatStateUsing(fn ($state) => LoanStatus::from($state)->name()),
                TextColumn::make('disbursed_amount')
                    ->label('Đã giải ngân')
                    ->alignCenter()
                    ->money('VND'),
                TextColumn::make('total_due_amount')
                    ->label('Tổng phải trả')
                    ->alignCenter()
                    ->money('VND'),
                TextColumn::make('total_paid_amount')
                    ->label('Đã trả')
                    ->alignCenter()
                    ->money('VND'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        LoanStatus::PENDING->value => LoanStatus::PENDING->name(),
                        LoanStatus::APPROVED->value => LoanStatus::APPROVED->name(),
                        LoanStatus::REJECTED->value => LoanStatus::REJECTED->name(),
                        LoanStatus::ACTIVE->value => LoanStatus::ACTIVE->name(),
                        LoanStatus::COMPLETED->value => LoanStatus::COMPLETED->name(),
                    ]),
                TrashedFilter::make(),
            ])
            ->searchable(['user.name', 'user.phone', 'user.email', 'user.address', 'id'])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem chi tiết')
                        ->icon('heroicon-o-eye'),
                    
                    Action::make('approve')
                        ->label('Duyệt đơn vay')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === LoanStatus::PENDING->value)
                        ->form([
                            Select::make('mode')
                                ->label('Chế độ')
                                ->live()
                                ->options([
                                    'approve_only' => 'Chỉ duyệt (chưa giải ngân)',
                                    'approve_and_disburse' => 'Duyệt và giải ngân',
                                ])
                                ->required()
                                ->default('approve_only'),
                            DatePicker::make('start_date')
                                ->label('Ngày bắt đầu vay')
                                ->default(now())
                                ->visible(fn ($get) => $get('mode') === 'approve_and_disburse'),
                            TextInput::make('disbursed_amount')
                                ->label('Số tiền giải ngân (VNĐ)')
                                ->numeric()
                                ->default(fn ($record) => $record->principal_amount)
                                ->minValue(0)
                                ->maxValue(fn ($record) => $record->principal_amount)
                                ->suffix('VNĐ')
                                ->helperText(function ($record) {
                                    $principalAmount = number_format($record->principal_amount);
                                    return "Số tiền vay: {$principalAmount} VNĐ. Số tiền giải ngân có thể nhỏ hơn hoặc bằng số tiền vay.";
                                })
                                ->visible(fn ($get) => $get('mode') === 'approve_and_disburse')
                                ->required(fn ($get) => $get('mode') === 'approve_and_disburse'),
                            Placeholder::make('payment_qr')
                                ->label('QR thanh toán')
                                ->visible(fn ($get) => $get('mode') === 'approve_and_disburse')
                                ->content(function ($record, $get) {
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
                                        '<div class="space-y-3 rounded-lg border border-gray-200 p-4">'
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
                        ->action(function ($record, array $data) {
                            try {
                                app(UserLoanApprovalService::class)->approve(
                                    $record,
                                    $data['mode'] ?? 'approve_only',
                                    $data['start_date'] ?? null,
                                    isset($data['disbursed_amount']) ? (float) $data['disbursed_amount'] : null,
                                );

                                $message = ($data['mode'] ?? 'approve_only') === 'approve_and_disburse'
                                    ? 'Duyệt và giải ngân khoản vay thành công.'
                                    : 'Duyệt khoản vay thành công.';

                                Notification::make()
                                    ->title($message)
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Duyệt khoản vay thất bại')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    
                    Action::make('reject')
                        ->label('Từ chối đơn vay')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === LoanStatus::PENDING->value)
                        ->form([
                            Textarea::make('reject_reason')
                                ->label('Lý do từ chối')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                app(UserLoanApprovalService::class)->reject($record, $data['reject_reason']);

                                Notification::make()
                                    ->title('Từ chối đơn vay thành công')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Từ chối đơn vay thất bại')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    
                    Action::make('send_notification')
                        ->label('Gửi thông báo')
                        ->icon('heroicon-o-bell')
                        ->color('info')
                        ->form([
                            Select::make('notification_type')
                                ->label('Loại thông báo')
                                ->options([
                                    'approval' => 'Thông báo duyệt đơn',
                                    'rejection' => 'Thông báo từ chối',
                                    'payment_reminder' => 'Nhắc nhở thanh toán',
                                    'overdue' => 'Thông báo quá hạn',
                                ])
                                ->required(),
                            Textarea::make('message')
                                ->label('Nội dung thông báo')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            Notification::make()
                                ->title('Gửi thông báo thành công')
                                ->success()
                                ->send();
                        }),
                    
                    EditAction::make()
                        ->label('Điều chỉnh đơn vay')
                        ->icon('heroicon-o-pencil'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
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
