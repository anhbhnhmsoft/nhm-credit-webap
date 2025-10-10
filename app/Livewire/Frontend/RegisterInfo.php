<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class RegisterInfo extends Component
{
    public string $fullName = '';
    public string $email = '';
    public string $password = '';
    public string $confirmPassword = '';

    public function submit()
    {
    }

    public function render()
    {
        return view('livewire.frontend.auth.register-info');
    }
}