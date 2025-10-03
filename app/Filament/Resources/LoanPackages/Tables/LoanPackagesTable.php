<?php

namespace App\Filament\Resources\LoanPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LoanPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('id')->label('ID'),
            TextColumn::make('config_loans_name')
                ->label('Tên gói')
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'name', '')),
            TextColumn::make('config_loans_term')
                ->label('Kỳ hạn (tháng)')
                ->alignCenter()
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'term_month', '')),
            TextColumn::make('config_loans_rate')
                ->label('Lãi suất')
                ->alignCenter()
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'interest_rate', ''))
                ->suffix('%'),
            TextColumn::make('config_loans_penalty')
                ->label('Phạt trả chậm')
                ->alignCenter()
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'penalty_rate', ''))
                ->suffix('%'),
            TextColumn::make('config_loans_active')
                ->label('Kích hoạt')
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'active', false) ? 'Hoạt động' : 'Không hoạt động'),
        ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
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
