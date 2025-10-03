<?php

namespace App\Filament\Resources\UserLoans\Schemas;

use App\Models\LoanPackage;
use App\Models\User;
use App\Services\LoanCalculationService;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\RoleUser;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserLoansForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Khách hàng')
                    ->options(fn () => User::query()
                        ->where('role', RoleUser::CUSTOMER->value)
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
                        } else {
                            $set('user_name', null);
                            $set('user_phone', null);
                            $set('user_email', null);
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

                Select::make('loan_package_id')
                    ->label('Gói vay')
                    ->options(fn () => LoanPackage::query()
                        ->where('config_loans->active', true)
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(function ($package) {
                            $config = $package->config_loans;
                            if (!is_array($config)) {
                                $config = json_decode($config ?? '{}', true) ?: [];
                            }
                            $name = $config['name'] ?? 'Gói vay';
                            $termMonth = $config['term_month'] ?? 0;
                            $interestRate = $config['interest_rate'] ?? 0;
                            return [$package->id => "{$name} - {$termMonth} tháng - {$interestRate}%"];
                        }))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $package = LoanPackage::find($state);
                            if ($package) {
                                $config = $package->config_loans;
                                if (!is_array($config)) {
                                    $config = json_decode($config ?? '{}', true) ?: [];
                                }
                                $set('term_months', $config['term_month'] ?? 0);
                                $set('interest_rate_year', $config['interest_rate'] ?? 0);
                            }
                        } else {
                            $set('term_months', 0);
                            $set('interest_rate_year', 0);
                        }
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state) {
                            $package = LoanPackage::find($state);
                            if ($package) {
                                $config = $package->config_loans;
                                if (!is_array($config)) {
                                    $config = json_decode($config ?? '{}', true) ?: [];
                                }
                                $set('term_months', $config['term_month'] ?? 0);
                                $set('interest_rate_year', $config['interest_rate'] ?? 0);
                            }
                        }
                    }),

                TextInput::make('principal_amount')
                    ->label('Số tiền gốc (VND)')
                    ->numeric()
                    ->required()
                    ->suffix('VND')
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('interest_rate_year') && $get('term_months')) {
                            $service = app(LoanCalculationService::class);
                            $totalDueAmount = $service->calcAmount(
                                $state, 
                                $get('interest_rate_year'), 
                                $get('term_months'), 
                                $get('service_fee_amount') ?? 0
                            );
                            $set('total_due_amount', $totalDueAmount);
                        }
                    }),

                TextInput::make('term_months')
                    ->label('Kỳ hạn (tháng)')
                    ->numeric()
                    ->required()
                    ->disabled()
                    ->suffix('tháng')
                    ->dehydrated(true),

                TextInput::make('interest_rate_year')
                    ->label('Lãi suất năm (%)')
                    ->numeric()
                    ->required()
                    ->disabled()
                    ->suffix('%')
                    ->dehydrated(true),

                TextInput::make('service_fee_amount')
                    ->label('Phí dịch vụ (VND)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND')
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('principal_amount') && $get('interest_rate_year') && $get('term_months')) {
                            $service = app(LoanCalculationService::class);
                            $totalDueAmount = $service->calcAmount(
                                $get('principal_amount'), 
                                $get('interest_rate_year'), 
                                $get('term_months'), 
                                $state
                            );
                            $set('total_due_amount', $totalDueAmount);
                        }
                    }),

                TextInput::make('total_due_amount')
                    ->label('Tổng số tiền phải trả (VND)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->suffix('VND'),

                TextInput::make('disbursed_amount')
                    ->label('Số tiền đã giải ngân (VND)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required()
                    ->validationMessages([
                        'min' => 'Số tiền đã giải ngân không được nhỏ hơn 0.',
                        'required' => 'Số tiền đã giải ngân không được để trống.',
                    ])
                    ->suffix('VND'),

                DatePicker::make('start_date')
                    ->label('Ngày bắt đầu vay')
                    ->default(now())
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('term_months')) {
                            $set('due_date', ($state instanceof Carbon ? $state->copy() : Carbon::parse($state))->addMonths((int) $get('term_months')));
                        }
                    }),

                DatePicker::make('due_date')
                    ->label('Ngày đến hạn')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        LoanStatus::PENDING->value => LoanStatus::PENDING->name(),
                        LoanStatus::APPROVED->value => LoanStatus::APPROVED->name(),
                        LoanStatus::ACTIVE->value => LoanStatus::ACTIVE->name(),
                        LoanStatus::COMPLETED->value => LoanStatus::COMPLETED->name(),
                        LoanStatus::REJECTED->value => LoanStatus::REJECTED->name(),
                    ])
                    ->default(LoanStatus::PENDING->value)
                    ->required(),

                Textarea::make('reject_reason')
                    ->label('Lý do từ chối')
                    ->rows(3)
                    ->visible(fn (callable $get) => $get('status') == LoanStatus::REJECTED->value),
            ])
            ->columns(2);
    }


}
