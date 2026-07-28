<?php

namespace App\Filament\Resources\LoanPackages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use App\Models\LoanPackage;

class LoanPackagesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
                    TextInput::make('config_loans.name')
                        ->label('Tên gói')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('config_loans.term_month')
                        ->label('Kỳ hạn (ngày)')
                        ->placeholder('7, 14')
                        ->helperText('Nhập số ngày cách nhau bằng dấu phẩy. Ví dụ: 7, 14 ngày'),
                    TextInput::make('config_loans.interest_rate')
                        ->label('Lãi suất (%)')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->required(),
                    TextInput::make('config_loans.penalty_rate')
                        ->label('Phạt trả chậm (%)')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->required(),
                    TextInput::make('config_loans.min_amount')
                        ->label('Số tiền vay tối thiểu (VNĐ)')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->helperText('Ví dụ: 1,000,000'),
                    TextInput::make('config_loans.max_amount')
                        ->label('Số tiền vay tối đa (VNĐ)')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->helperText('Ví dụ: 20,000,000'),
                    Toggle::make('config_loans.active')
                        ->label('Kích hoạt')
                        ->default(false)
                        ->helperText('Chỉ có thể có 1 gói vay hoạt động tại một thời điểm')
                        ->live()
                        ->afterStateUpdated(function ($state, $component) {
                            if ($state) {
                                $activePackage = LoanPackage::whereJsonContains('config_loans->active', true)->first();
                                
                                if ($activePackage) {
                                    Notification::make()
                                        ->title('Cảnh báo')
                                        ->body('Gói vay "' . data_get($activePackage->config_loans, 'name', '') . '" đang hoạt động. Khi lưu, gói này sẽ được tắt.')
                                        ->warning()
                                        ->send();
                                }
                            }
                        }),
        ])->columns(2);
    }
}
