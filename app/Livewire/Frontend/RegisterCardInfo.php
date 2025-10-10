<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class RegisterCardInfo extends Component
{
    public string $cardName = '';
    public string $cardNumber = '';
    public string $frontImage = '';
    public string $idSelfie = '';

    public function submit()
    {
    }

    public function render()
    {
        return view('livewire.frontend.auth.register-card-info');
    }
}