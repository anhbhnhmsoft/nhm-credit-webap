<?php

namespace App\Livewire\Frontend;

use App\Models\PageStatic;
use App\Services\PageStaticService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class PageStaticPage extends Component
{
    public string $slug;
    public PageStatic $page;

    public function mount(string $slug, PageStaticService $service): void
    {
        $this->slug = $slug;
        $this->page = $service->getActiveBySlug($slug);
    }

    public function render()
    {
        return view('livewire.frontend.page-static-page');
    }
}


