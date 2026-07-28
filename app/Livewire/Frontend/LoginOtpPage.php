<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LoginOtpPage extends Component
{
    public function render()
    {
        return view('livewire.frontend.auth.login-otp');
    }
}


