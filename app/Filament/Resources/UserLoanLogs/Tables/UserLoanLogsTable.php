<?php

namespace App\Filament\Resources\UserLoanLogs\Tables;

use App\Services\PaymentService;
use App\Utils\Constants\LoanLogStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UserLoanLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('userLoan.user.name')
                    ->label('Khách hàng')
                    ->searchable(),
                TextColumn::make('userLoan.user.phone')
                    ->label('SĐT')
                    ->searchable(),
                TextColumn::make('userLoan.principal_amount')
                    ->label('Số tiền vay')
                    ->money('VND')
                    ->alignCenter(),
                TextColumn::make('installment_no')
                    ->label('Kỳ hạn (ngày)')
                    ->alignCenter(),
                TextColumn::make('due_date')
                    ->label('Đến hạn')
                    ->date('d/m/Y')
                    ->alignCenter(),
                TextColumn::make('actual_due_date')
                    ->label('Ngày thực trả')
                    ->date('d/m/Y')
                    ->placeholder('Chưa thanh toán')
                    ->alignCenter(),
                TextColumn::make('principal_due')
                    ->label('Tiền phải trả')
                    ->money('VND')
                    ->alignCenter(),
                TextColumn::make('interest_due')
                    ->label('Lãi')
                    ->money('VND')
                    ->alignCenter(),
                TextColumn::make('fee_due')
                    ->label('Phí')
                    ->money('VND')
                    ->alignCenter(),
                TextColumn::make('total_paid')
                    ->label('Đã trả')
                    ->money('VND')
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->colors([
                        'warning' => LoanLogStatus::PENDING->value,
                        'info' => defined('App\\Utils\\Constants\\LoanLogStatus::PARTIAL') ? LoanLogStatus::PARTIAL->value : null,
                        'success' => LoanLogStatus::PAID->value,
                        'danger' => LoanLogStatus::OVERDUE->value,
                    ])
                    ->formatStateUsing(fn($state) => LoanLogStatus::from($state)->name()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        LoanLogStatus::PENDING->value => LoanLogStatus::PENDING->name(),
                        LoanLogStatus::PAID->value => LoanLogStatus::PAID->name(),
                        LoanLogStatus::OVERDUE->value => LoanLogStatus::OVERDUE->name(),
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('update_status')
                    ->label('Trạng thái thanh toán')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== LoanLogStatus::PAID->value)
                    ->form([
                        Select::make('mode')
                            ->label('Chế độ')
                            ->options([
                                'pay' => 'Thanh toán đầy đủ',
                                'part' => 'Thanh toán một phần',
                                'overdue' => 'Quá hạn',
                            ])
                            ->required()
                            ->live()
                            ->default('pay'),
                        DatePicker::make('actual_due_date')
                            ->label('Ngày thanh toán')
                            ->default(now())
                            ->visible(fn($get) => in_array($get('mode'), ['pay','part'])),
                        TextInput::make('payment_amount')
                            ->label('Số tiền thanh toán')
                            ->numeric()
                            ->default(fn($record) => max((float)($record->principal_due + $record->interest_due + $record->fee_due) - (float)($record->total_paid ?? 0), 0))
                            ->visible(fn (Get $get) => $get('mode') === 'part')
                            ->required(fn (Get $get) => $get('mode') === 'part'),
                    ])
                    ->action(function ($record, array $data) {
                        $mode = $data['mode'] ?? 'pay';
                        $actualDate = $data['actual_due_date'] ?? now();

                        if ($mode === 'overdue') {
                            $record->update(['status' => LoanLogStatus::OVERDUE->value]);
                            
                            Notification::make()
                                ->title('Đã đánh dấu quá hạn')
                                ->success()
                                ->send();
                        } else {
                            $totalDue = (float)($record->principal_due + $record->interest_due + $record->fee_due);
                            $currentPaid = (float)($record->total_paid ?? 0);
                            $remainingAmount = $totalDue - $currentPaid;
                            
                            $payAmount = $mode === 'pay'
                                ? max($remainingAmount, 0)
                                : (float)($data['payment_amount'] ?? 0);
                            
                            if ($payAmount <= 0) {
                                Notification::make()
                                    ->title('Lỗi')
                                    ->body('Số tiền thanh toán phải lớn hơn 0')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            if ($payAmount > $remainingAmount) {
                                Notification::make()
                                    ->title('Lỗi')
                                    ->body("Số tiền thanh toán ({$payAmount}) không được vượt quá số tiền còn lại ({$remainingAmount})")
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            try {
                                $paymentService = app(PaymentService::class);
                                $payment = $paymentService->createLoanPayment(
                                    $record, 
                                    $payAmount, 
                                    "Thanh toán kỳ {$record->installment_no} - " . number_format($payAmount) . " VNĐ"
                                );
                                
                                $record->update(['actual_due_date' => $actualDate]);
                                
                                Notification::make()
                                    ->title('Thanh toán thành công')
                                    ->body("Đã tạo giao dịch thanh toán {$payment->transaction_code} với số tiền " . number_format($payAmount) . " VNĐ")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Lỗi tạo giao dịch thanh toán')
                                    ->body('Có lỗi khi tạo giao dịch: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
                    Action::make('edit')
                    ->label('Chỉnh sửa')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => route('filament.admin.resources.user-loan-logs.edit', $record))
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
