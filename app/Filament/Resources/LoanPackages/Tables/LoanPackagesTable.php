<?php

namespace App\Filament\Resources\LoanPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Models\LoanPackage;

class LoanPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->defaultSort('created_at', 'desc')
        ->columns([
            TextColumn::make('id')->label('ID'),
            TextColumn::make('config_loans_name')
                ->label('Tên gói')
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'name', '')),
            TextColumn::make('config_loans_term')
                ->label('Kỳ hạn (tháng)')
                ->alignCenter()
                ->getStateUsing(function ($record) {
                    $termMonths = data_get($record->config_loans, 'term_month', []);
                    if (is_array($termMonths)) {
                        return implode(', ', $termMonths) . ' tháng';
                    }
                    return $termMonths . ' tháng';
                }),
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
            TextColumn::make('config_loans_min_amount')
                ->label('Hạn mức tối thiểu')
                ->alignCenter()
                ->getStateUsing(fn ($record) => number_format(data_get($record->config_loans, 'min_amount', 0)))
                ->suffix(' VNĐ'),
            TextColumn::make('config_loans_max_amount')
                ->label('Hạn mức tối đa')
                ->alignCenter()
                ->getStateUsing(fn ($record) => number_format(data_get($record->config_loans, 'max_amount', 0)))
                ->suffix(' VNĐ'),
            ToggleColumn::make('config_loans_active')
                ->label('Kích hoạt')
                ->getStateUsing(fn ($record) => data_get($record->config_loans, 'active', false))
                ->updateStateUsing(function ($record, $state) {
                    if ($state) {
                        LoanPackage::where('id', '!=', $record->id)
                            ->get()
                            ->each(function ($package) {
                                $config = $package->config_loans;
                                $config['active'] = false;
                                $package->update(['config_loans' => $config]);
                            });
                        
                        $config = $record->config_loans;
                        $config['active'] = true;
                        $record->update(['config_loans' => $config]);
                        
                        Notification::make()
                            ->title('Gói vay đã được kích hoạt')
                            ->body('Gói vay "' . data_get($record->config_loans, 'name', '') . '" đã được kích hoạt và các gói khác đã được tắt.')
                            ->success()
                            ->send();
                    } else {
                        $config = $record->config_loans;
                        $config['active'] = false;
                        $record->update(['config_loans' => $config]);
                        
                        Notification::make()
                            ->title('Gói vay đã được tắt')
                            ->body('Gói vay "' . data_get($record->config_loans, 'name', '') . '" đã được tắt.')
                            ->warning()
                            ->send();
                    }
                })
                ->disabled(fn ($record) => data_get($record->config_loans, 'active', false))
                ->tooltip(fn ($record) => data_get($record->config_loans, 'active', false) 
                    ? 'Gói này đang hoạt động. Tắt để thay đổi.' 
                    : 'Bật để kích hoạt gói vay này'),
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
