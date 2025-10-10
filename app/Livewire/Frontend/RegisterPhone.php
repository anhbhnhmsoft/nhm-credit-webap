<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;

class RegisterPhone extends Component
{
    public string $phone = '';

    public function submit()
    {
        $this->validate(['phone' => 'required|string|max:20|unique:users,phone']);

    }

    public function render()
    {
        return view('livewire.frontend.auth.register-phone');
    }
}