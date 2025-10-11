<?php

namespace App\Filament\Resources\UserLoans\Schemas;

use App\Models\Bank;
use App\Models\LoanPackage;
use App\Models\User;
use App\Services\LoanCalculationService;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\RoleUser;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
                    ->label('Chọn khách hàng')
                    ->options(fn() => User::query()
                        ->where('role', RoleUser::CUSTOMER->value)
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->placeholder('Chọn khách hàng có sẵn hoặc để trống để tạo mới')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $user = User::find($state);
                            if ($user) {
                                $set('user_name', $user->name);
                                $set('user_phone', $user->phone);
                                $set('user_email', $user->email);
                                $set('user_address', $user->address);
                                $set('front_image_card', $user->front_image_card);
                                $set('back_image_card', $user->back_image_card);
                                $set('id_card_selfie_path', $user->id_card_selfie_path);
                                
                                $bankAccount = \App\Models\UserBankAccount::where('user_id', $state)->first();
                                if ($bankAccount) {
                                    $set('bank_id', $bankAccount->bank_id);
                                    $set('bank_name', $bankAccount->bank_name);
                                    $set('account_number', $bankAccount->account_number);
                                    $set('account_name', $bankAccount->account_name);
                                }
                            }
                        } else {
                            $set('user_name', null);
                            $set('user_phone', null);
                            $set('user_email', null);
                            $set('user_address', null);
                            $set('front_image_card', null);
                            $set('back_image_card', null);
                            $set('id_card_selfie_path', null);
                            $set('bank_id', null);
                            $set('bank_name', null);
                            $set('account_number', null);
                            $set('account_name', null);
                        }
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state) {
                            $user = User::find($state);
                            if ($user) {
                                $set('user_name', $user->name);
                                $set('user_phone', $user->phone);
                                $set('user_email', $user->email);
                                $set('user_address', $user->address);
                                $set('front_image_card', $user->front_image_card);
                                $set('back_image_card', $user->back_image_card);
                                $set('id_card_selfie_path', $user->id_card_selfie_path);
                                
                                $bankAccount = \App\Models\UserBankAccount::where('user_id', $state)->first();
                                if ($bankAccount) {
                                    $set('bank_id', $bankAccount->bank_id);
                                    $set('bank_name', $bankAccount->bank_name);
                                    $set('account_number', $bankAccount->account_number);
                                    $set('account_name', $bankAccount->account_name);
                                }
                            }
                        }
                    }),

                TextInput::make('user_name')
                    ->label('Tên khách hàng')
                    ->required()
                    ->placeholder('Nhập tên khách hàng')
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && !$get('user_id')) {
                            $set('user_phone', null);
                            $set('user_email', null);
                            $set('user_address', null);
                            $set('front_image_card', null);
                            $set('back_image_card', null);
                            $set('id_card_selfie_path', null);
                        }
                    }),

                TextInput::make('user_phone')
                    ->label('Số điện thoại')
                    ->required()
                    ->placeholder('Nhập số điện thoại')
                    ->live(debounce: 500)
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (!empty($value) && !$get('user_id')) {
                                    $existingUser = \App\Models\User::where('phone', $value)->first();
                                    if ($existingUser) {
                                        $fail("Số điện thoại '{$value}' đã được sử dụng bởi người dùng khác.");
                                    }
                                }
                            };
                        },
                    ]),

                TextInput::make('user_email')
                    ->label('Email')
                    ->email()
                    ->placeholder('Nhập email khách hàng')
                    ->live(debounce: 500)
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (!empty($value) && !$get('user_id')) {
                                    $existingUser = \App\Models\User::where('email', $value)->first();
                                    if ($existingUser) {
                                        $fail("Email '{$value}' đã được sử dụng bởi người dùng khác.");
                                    }
                                }
                            };
                        },
                    ]),

                TextInput::make('user_number_card')
                    ->label('CCCD/CMND')
                    ->required()
                    ->placeholder('Nhập số CCCD/CMND')
                    ->live(debounce: 500),

                TextInput::make('bank_name')
                    ->label('Tên ngân hàng')
                    ->placeholder('Nhập tên ngân hàng hoặc chọn từ dropdown trên')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('bank_id', null);
                        }
                    }),

                TextInput::make('account_number')
                    ->label('Số tài khoản')
                    ->required()
                    ->placeholder('Nhập số tài khoản'),

                TextInput::make('account_name')
                    ->label('Tên chủ tài khoản')
                    ->required()
                    ->placeholder('Nhập tên chủ tài khoản'),

                TextInput::make('loan_package_id')
                    ->label('Gói vay')
                    ->default(1)
                    ->hidden()
                    ->dehydrated(true),

                TextInput::make('principal_amount')
                    ->label('Số tiền gốc (VND)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix('VND'),

                TextInput::make('term_months')
                    ->label('Kỳ hạn')
                    ->placeholder('Nhập kỳ hạn vay')
                    ->required(),

                TextInput::make('interest_rate_year')
                    ->label('Phí quá hạn')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),


                TextInput::make('service_fee_amount')
                    ->label('Phí dịch vụ (VND)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND'),

                TextInput::make('total_due_amount')
                    ->label('Tổng số tiền phải trả (VND)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix('VND'),

                TextInput::make('total_paid_amount')
                    ->label('Đã trả (VND)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('VND')
                    ->helperText('Số tiền đã thanh toán từ UserLoanLog sẽ được đồng bộ tự động')
                    ->visible(fn() => request()->routeIs('filament.admin.resources.user-loans.edit')),

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
                    ->required(),

                DatePicker::make('due_date')
                    ->label('Ngày đến hạn')
                    ->columnSpanFull()
                    ->required(),

                FileUpload::make('front_image_card')
                    ->label('Ảnh CCCD mặt trước')
                    ->image()
                    ->directory('user-documents')
                    ->visibility('private')
                    ->nullable(),

                FileUpload::make('back_image_card')
                    ->label('Ảnh CCCD mặt sau')
                    ->image()
                    ->directory('user-documents')
                    ->visibility('private')
                    ->nullable(),

                FileUpload::make('id_card_selfie_path')
                    ->label('Ảnh chụp chính chủ')
                    ->image()
                    ->directory('user-documents')
                    ->columnSpanFull()
                    ->visibility('private')
                    ->nullable(),
                Select::make('status')
                    ->label('Trạng thái')
                    ->columnSpanFull()
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
                    ->visible(fn(callable $get) => $get('status') == LoanStatus::REJECTED->value),
            ])
            ->columns(2);
    }

}
