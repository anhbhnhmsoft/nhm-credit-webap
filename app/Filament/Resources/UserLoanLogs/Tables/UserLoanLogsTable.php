<?php

namespace App\Filament\Resources\UserLoanLogs\Tables;

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
                    ->label('Kỳ')
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
                    ->label('Gốc')
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
                            ->visible(fn (Get $get) => $get('mode') === 'part'),
                    ])
                    ->action(function ($record, array $data) {
                        $mode = $data['mode'] ?? 'pay';
                        $payAmount = (float)($data['payment_amount'] ?? 0);
                        $actualDate = $data['actual_due_date'] ?? now();

                        $totalDue = (float)($record->principal_due + $record->interest_due + $record->fee_due);
                        $currentPaid = (float)($record->total_paid ?? 0);

                        $updates = [];

                        if ($mode === 'overdue') {
                            $updates['status'] = LoanLogStatus::OVERDUE->value;
                        } elseif ($mode === 'part') {
                            $updates['total_paid'] = $currentPaid + max($payAmount, 0);
                            $updates['actual_due_date'] = $actualDate;
                            $updates['status'] = ($updates['total_paid'] >= $totalDue)
                                ? LoanLogStatus::PAID->value
                                : (defined('App\\Utils\\Constants\\LoanLogStatus::PARTIAL') ? LoanLogStatus::PARTIAL->value : LoanLogStatus::PENDING->value);
                        } else { 
                            $updates['total_paid'] = max($currentPaid + max($payAmount, 0), $totalDue);
                            $updates['actual_due_date'] = $actualDate;
                            $updates['status'] = LoanLogStatus::PAID->value;
                        }

                        $record->update($updates);

                        Notification::make()
                            ->title('Cập nhật trạng thái thành công')
                            ->success()
                            ->send();
                    }),
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
