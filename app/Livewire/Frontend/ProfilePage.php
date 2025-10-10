<?php

namespace App\Livewire\Frontend;

use App\Services\PageStaticService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ProfilePage extends Component
{
    public function render()
    {
        $staticPages = app(PageStaticService::class)->listActiveOrdered();

        return view('livewire.frontend.profile-page', [
            'staticPages' => $staticPages,
        ]);
    }
}
