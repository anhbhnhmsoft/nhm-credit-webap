<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Services\ConfigService;
use App\Utils\Constants\ConfigName;

class MySettingPage extends Component
{
    public string $logoUrl = '';

    public function mount(ConfigService $configService)
    {
        $this->logoUrl = (string) $configService->getConfigValue(ConfigName::LOGO->value, '');
    }

    public function render()
    {
        return view('livewire.frontend.my-setting-page');
    }
}
