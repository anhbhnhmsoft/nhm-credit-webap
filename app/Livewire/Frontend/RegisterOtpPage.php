<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RegisterOtpPage extends Component
{
    public function render()
    {
        return view('livewire.frontend.auth.register-otp');
    }
}


