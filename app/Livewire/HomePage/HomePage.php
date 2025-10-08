<?php

namespace App\Livewire\HomePage;

use Livewire\Attributes\Layout;
use Livewire\Component;

class HomePage extends Component
{
    public int $amount = 2000000; // 2,000,000 mặc định
    public int $days = 180; // 180 ngày mặc định

    public array $quickAmounts = [2000000, 6500000, 11000000, 15500000, 20000000];

    public function setAmount(int $value): void
    {
        $this->amount = $value;
    }

    public function setDays(int $days): void
    {
        $this->days = $days;
    }

    public function updatedAmount(): void
    {
        if ($this->amount < 0) {
            $this->amount = 0;
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.frontend.home-page');
    }
}


