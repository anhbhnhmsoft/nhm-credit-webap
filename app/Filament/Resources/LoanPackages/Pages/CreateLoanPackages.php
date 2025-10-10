<?php

namespace App\Filament\Resources\LoanPackages\Pages;

use App\Filament\Resources\LoanPackages\LoanPackagesResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\LoanPackage;

class CreateLoanPackages extends CreateRecord
{
    protected static string $resource = LoanPackagesResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        $record = $this->record;
        $config = $record->config_loans;
        
        if (data_get($config, 'active', false)) {
            LoanPackage::where('id', '!=', $record->id)
                ->get()
                ->each(function ($package) {
                    $packageConfig = $package->config_loans;
                    $packageConfig['active'] = false;
                    $package->update(['config_loans' => $packageConfig]);
                });
            
            Notification::make()
                ->title('Gói vay đã được tạo và kích hoạt')
                ->body('Gói vay "' . data_get($config, 'name', '') . '" đã được tạo và kích hoạt. Các gói khác đã được tắt.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gói vay đã được tạo')
                ->body('Gói vay "' . data_get($config, 'name', '') . '" đã được tạo thành công.')
                ->success()
                ->send();
        }
    }
}
