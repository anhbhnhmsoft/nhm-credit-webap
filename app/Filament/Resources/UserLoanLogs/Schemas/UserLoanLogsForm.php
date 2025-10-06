<?php

namespace App\Filament\Resources\UserLoanLogs\Schemas;

use App\Models\UserLoan;
use App\Utils\Constants\LoanLogStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserLoanLogsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_loan_id')
                    ->label('Khoản vay')
                    ->options(fn () => UserLoan::query()
                        ->with('user')
                        ->latest('created_at')
                        ->get()
                        ->mapWithKeys(fn ($loan) => [
                            $loan->id => ($loan->user?->name ?: 'User') . ' - ' . (string) $loan->id,
                        ]))
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('installment_no')
                    ->label('Số kỳ trả')
                    ->numeric()
                    ->required(),

                DatePicker::make('due_date')
                    ->label('Ngày đến hạn')
                    ->required(),

                DatePicker::make('actual_due_date')
                    ->label('Ngày trả thực tế'),

                TextInput::make('principal_due')
                    ->label('Gốc phải trả')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND')
                    ->required(),

                TextInput::make('interest_due')
                    ->label('Lãi phải trả')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND')
                    ->required(),

                TextInput::make('fee_due')
                    ->label('Phí phải trả')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND'),

                TextInput::make('total_paid')
                    ->label('Tổng đã trả')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND')
                    ->required(),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        LoanLogStatus::PENDING->value => LoanLogStatus::PENDING->name(),
                        LoanLogStatus::PAID->value => LoanLogStatus::PAID->name(),
                        LoanLogStatus::OVERDUE->value => LoanLogStatus::OVERDUE->name(),
                    ])
                    ->required(),
            ])
            ->columns(2);
    }
}
