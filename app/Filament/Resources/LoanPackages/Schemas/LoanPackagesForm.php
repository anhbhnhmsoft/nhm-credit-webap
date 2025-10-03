<?php

namespace App\Filament\Resources\LoanPackages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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
                        ->label('Kỳ hạn (tháng)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
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
                    Toggle::make('config_loans.active')
                        ->label('Kích hoạt')
                        ->default(true),
        ])->columns(2);
    }
}
