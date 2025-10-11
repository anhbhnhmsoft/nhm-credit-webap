<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\User;
use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Utils\Constants\LoanLogStatus;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\PaymentDirection;
use App\Utils\Constants\PaymentStatus;
use App\Utils\Constants\RoleUser;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                ->label('Khách hàng')
                ->options(fn () => User::query()
                    ->where('role', RoleUser::CUSTOMER->value) // CUSTOMER role
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $user = User::find($state);
                        if ($user) {
                            $set('user_name', $user->name);
                            $set('user_phone', $user->phone);
                            $set('user_email', $user->email);
                        }
                            $set('user_loan_id', null);
                            $set('user_loan_log_id', null);
                    } else {
                        $set('user_name', null);
                        $set('user_phone', null);
                        $set('user_email', null);
                            $set('user_loan_id', null);
                            $set('user_loan_log_id', null);
                    }
                })
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state) {
                            $user = User::find($state);
                            if ($user) {
                                $set('user_name', $user->name);
                                $set('user_phone', $user->phone);
                                $set('user_email', $user->email);
                            }
                        }
                    }),

            TextInput::make('user_name')
                ->label('Tên khách hàng')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('user_phone')
                ->label('Số điện thoại')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('user_email')
                ->label('Email')
                ->disabled()
                ->dehydrated(false),
                Select::make('user_loan_id')
                    ->label('Khoản vay')
                    ->options(function (callable $get) {
                        $userId = $get('user_id');
                        if (!$userId) {
                            return [];
                        }
                        return UserLoan::query()
                            ->where('user_id', $userId)
                            ->whereIn('status', [LoanStatus::APPROVED->value, LoanStatus::ACTIVE->value]) // APPROVED, ACTIVE
                            ->with('user')
                            ->orderByDesc('created_at')
                            ->get()
                            ->mapWithKeys(function ($loan) {
                                return [
                                    $loan->id => "ID: {$loan->id} - " . number_format($loan->principal_amount) . " VNĐ - Kỳ: {$loan->term_months}",
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->visible(fn (callable $get) => (bool) $get('user_id'))
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('user_loan_log_id', null);
                    }),

                Select::make('user_loan_log_id')
                    ->label('Kỳ trả nợ')
                    ->options(function (callable $get) {
                        $loanId = $get('user_loan_id');
                        if (!$loanId) {
                            return [];
                        }

                        return UserLoanLog::query()
                            ->where('user_loan_id', $loanId)
                            ->where('status', LoanLogStatus::PENDING->value) // PENDING
                            ->get()
                            ->mapWithKeys(function ($log) {
                                $moneyToBePaid = $log->principal_due+$log->interest_due+$log->fee_due;
                                return [$log->id => "Kỳ {$log->installment_no} - Cần trả: " . number_format($moneyToBePaid) . " VNĐ"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->visible(fn (callable $get) => $get('user_loan_id') !== null),

                TextInput::make('transaction_code')
                    ->label('Mã giao dịch')
                    ->disabled()
                    ->unique()
                    ->placeholder('Tự động tạo nếu để trống')
                    ->validationMessages([
                        'unique' => 'Mã giao dịch đã tồn tại.',
                    ]),

                TextInput::make('amount')
                    ->label('Số tiền (VNĐ)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix('VNĐ')
                    ->helperText('Số tiền giao dịch'),

                Select::make('direction')
                    ->label('Loại giao dịch')
                    ->options([
                        PaymentDirection::IN->value => PaymentDirection::IN->name(),
                        PaymentDirection::OUT->value => PaymentDirection::OUT->name(),
                    ])
                    ->required()
                    ->default(PaymentDirection::IN->value)
                    ->helperText('Thu vào: Khách trả tiền, Chi ra: Giải ngân cho khách hàng'),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        PaymentStatus::PENDING->value => PaymentStatus::PENDING->name(),
                        PaymentStatus::SUCCESS->value => PaymentStatus::SUCCESS->name(),
                        PaymentStatus::FAILED->value => PaymentStatus::FAILED->name(),
                    ])
                    ->required()
                    ->default(PaymentStatus::PENDING->value),

                DatePicker::make('created_at')
                    ->label('Ngày giao dịch')
                    ->default(now())
                    ->required(),

                Textarea::make('description')
                    ->label('Ghi chú')
                    ->rows(3)
                    ->placeholder('Ghi chú thêm về giao dịch...')
                    ->helperText('Ghi chú thêm về giao dịch'),
            ])
            ->columns(2);
    }
}
