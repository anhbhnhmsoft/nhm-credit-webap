<?php

namespace App\Filament\Resources\LoanPackages\Pages;

use App\Filament\Resources\LoanPackages\LoanPackagesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\LoanPackage;

class EditLoanPackages extends EditRecord
{
    protected static string $resource = LoanPackagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['config_loans']['term_month']) && is_array($data['config_loans']['term_month'])) {
            $data['config_loans']['term_month'] = implode(', ', $data['config_loans']['term_month']);
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['config_loans']['term_month']) && is_string($data['config_loans']['term_month'])) {
            $termText = $data['config_loans']['term_month'];
            $months = array_map('trim', explode(',', $termText));
            $months = array_map('intval', $months);
            $months = array_filter($months, fn($m) => $m > 0);
            $data['config_loans']['term_month'] = array_values($months);
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $config = $record->config_loans;
        $wasActive = data_get($record->getOriginal('config_loans'), 'active', false);
        $isActive = data_get($config, 'active', false);
        
        if ($isActive) {
            LoanPackage::where('id', '!=', $record->id)
                ->get()
                ->each(function ($package) {
                    $packageConfig = $package->config_loans;
                    $packageConfig['active'] = false;
                    $package->update(['config_loans' => $packageConfig]);
                });
            
            if (!$wasActive) {
                Notification::make()
                    ->title('Gói vay đã được kích hoạt')
                    ->body('Gói vay "' . data_get($config, 'name', '') . '" đã được kích hoạt. Các gói khác đã được tắt.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Gói vay đã được cập nhật')
                    ->body('Gói vay "' . data_get($config, 'name', '') . '" đã được cập nhật và vẫn hoạt động. Các gói khác đã được tắt.')
                    ->success()
                    ->send();
            }
        } 
        elseif (!$isActive && $wasActive) {
            Notification::make()
                ->title('Gói vay đã được tắt')
                ->body('Gói vay "' . data_get($config, 'name', '') . '" đã được tắt.')
                ->warning()
                ->send();
        }
        else {
            Notification::make()
                ->title('Gói vay đã được cập nhật')
                ->body('Thông tin gói vay "' . data_get($config, 'name', '') . '" đã được cập nhật thành công.')
                ->success()
                ->send();
        }
    }
}
