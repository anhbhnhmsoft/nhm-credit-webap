<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class RegisterPage extends Component
{
    public function render()
    {
        return view('livewire.frontend.auth.register');
    }
}
