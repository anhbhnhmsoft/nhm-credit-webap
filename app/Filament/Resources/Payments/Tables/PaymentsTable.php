<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Utils\Constants\PaymentDirection;
use App\Utils\Constants\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_code')
                    ->label('Mã GD')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Đã sao chép mã giao dịch'),

                TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.phone')
                    ->label('SĐT')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('userLoan.id')
                    ->label('Khoản vay')
                    ->formatStateUsing(fn ($state) => $state ? "ID: {$state}" : '-')
                    ->sortable(),

                TextColumn::make('userLoanLog.installment_no')
                    ->label('Kỳ trả')
                    ->formatStateUsing(fn ($state) => $state ? "Kỳ {$state}" : '-')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Số tiền')
                    ->money('VND')
                    ->sortable()
                    ->alignEnd(),

                BadgeColumn::make('direction')
                    ->label('Loại GD')
                    ->formatStateUsing(fn ($state) => PaymentDirection::from($state)->name())
                    ->colors([
                        'success' => PaymentDirection::IN->value,
                        'danger' => PaymentDirection::OUT->value,
                    ])
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(fn ($state) => PaymentStatus::from($state)->name())
                    ->colors([
                        'warning' => PaymentStatus::PENDING->value,
                        'success' => PaymentStatus::SUCCESS->value,
                        'danger' => PaymentStatus::FAILED->value,
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày GD')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('direction')
                    ->label('Loại giao dịch')
                    ->options([
                        PaymentDirection::IN->value => PaymentDirection::IN->name(),
                        PaymentDirection::OUT->value => PaymentDirection::OUT->name(),
                    ]),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        PaymentStatus::PENDING->value => PaymentStatus::PENDING->name(),
                        PaymentStatus::SUCCESS->value => PaymentStatus::SUCCESS->name(),
                        PaymentStatus::FAILED->value => PaymentStatus::FAILED->name(),
                    ]),
                TrashedFilter::make(),
            ])
            ->searchable(['transaction_code', 'user.name', 'user.phone'])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem chi tiết')
                        ->icon('heroicon-o-eye'),
                    
                    Action::make('approve')
                        ->label('Duyệt giao dịch')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === PaymentStatus::PENDING->value)
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt giao dịch')
                        ->modalDescription('Bạn có chắc chắn muốn duyệt giao dịch này?')
                        ->action(function ($record) {
                            $record->update(['status' => PaymentStatus::SUCCESS->value]);
                            
                            Notification::make()
                                ->title('Duyệt giao dịch thành công')
                                ->success()
                                ->send();
                        }),
                    
                    Action::make('reject')
                        ->label('Từ chối giao dịch')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === PaymentStatus::PENDING->value)
                        ->requiresConfirmation()
                        ->modalHeading('Từ chối giao dịch')
                        ->modalDescription('Bạn có chắc chắn muốn từ chối giao dịch này?')
                        ->action(function ($record) {
                            $record->update(['status' => PaymentStatus::FAILED->value]);
                            
                            Notification::make()
                                ->title('Từ chối giao dịch thành công')
                                ->success()
                                ->send();
                        }),
                    
                    EditAction::make()
                        ->label('Chỉnh sửa')
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
