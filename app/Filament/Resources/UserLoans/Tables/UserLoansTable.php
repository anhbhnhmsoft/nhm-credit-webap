<?php

namespace App\Filament\Resources\UserLoans\Tables;

use App\Utils\Constants\LoanStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
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

class UserLoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Khách hàng')->searchable(),
                TextColumn::make('user.phone')->label('Số điện thoại')->searchable(),
                TextColumn::make('loanPackage.config_loans.name')->label('Gói vay'),
                TextColumn::make('principal_amount')
                    ->label('Số tiền vay')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('term_months')
                ->label('Kỳ hạn (tháng)')
                ->alignCenter(),
                TextColumn::make('interest_rate_year')
                    ->label('Lãi suất')
                    ->suffix('%')
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
                TextColumn::make('monthly_payment')
                    ->label('Trả hàng tháng')
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
            ->searchable(['user.name', 'user.phone', 'id'])
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
                                ->label('Số tiền giải ngân')
                                ->numeric()
                                ->default(fn ($record) => $record->principal_amount)
                                ->visible(fn ($get) => $get('mode') === 'approve_and_disburse'),
                        ])
                        ->action(function ($record, array $data) {
                            $isDisburse = ($data['mode'] ?? 'approve_only') === 'approve_and_disburse';
                            $disbursed = (float)($data['disbursed_amount'] ?? 0);

                            $status = ($isDisburse && $disbursed > 0)
                                ? LoanStatus::ACTIVE->value
                                : LoanStatus::APPROVED->value;

                            $updates = [
                                'status' => $status,
                            ];

                            if ($status === LoanStatus::ACTIVE->value) {
                                $updates['start_date'] = $data['start_date'] ?? now();
                                $updates['disbursed_amount'] = $disbursed;
                            }

                            $record->update($updates);

                            Notification::make()
                                ->title($status === LoanStatus::ACTIVE->value ? 'Duyệt và giải ngân thành công' : 'Duyệt đơn vay thành công')
                                ->success()
                                ->send();
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
                            $record->update([
                                'status' => LoanStatus::REJECTED->value,
                                'reject_reason' => $data['reject_reason'],
                            ]);
                            
                            Notification::make()
                                ->title('Từ chối đơn vay thành công')
                                ->success()
                                ->send();
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
}
