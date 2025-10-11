<?php

namespace App\Livewire\Filament;

use App\Services\ConfigService;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Utils\Constants\StoragePath;
use Illuminate\Support\Arr;

class ConfigForm extends Component
{
    use WithFileUploads;
    private ConfigService $service;

    public array $config_value = [];

    public $configList;


    public function boot(ConfigService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->configList = $this->service->getAllConfig();
        foreach ($this->configList as $config) {
            $this->config_value[$config->config_key] = $config->config_value;
        }
    }

    public function updateConfig()
    {
        $imageFields = ['LOGO', 'QR_IMAGE'];
        
        foreach ($imageFields as $field) {
            if (Arr::exists($this->config_value, $field) && $this->config_value[$field] instanceof TemporaryUploadedFile) {
                $storedPath = $this->config_value[$field]->store(StoragePath::CONFIG_PATH->value, 'public');
                $this->config_value[$field] = $storedPath;
            }
        }

        $result = $this->service->updateConfigs($this->config_value);
        if ($result) {
            Notification::make()
                ->title('Cập nhật config thành công')
                ->success()
                ->send();
        }else{
            Notification::make()
                ->title('Cập nhật config thất bại')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.filament.config-form');
    }
}
