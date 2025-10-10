<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginPage extends Component
{
    public string $phone = '';
    public string $otp = '';

    public function submit()
    {
        $this->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('phone', $this->phone)->first();

        if (!$user) {
            session()->flash('error', 'Số điện thoại chưa được đăng ký.');
            return null;
        }

        Auth::login($user);
        return redirect()->route('profile');
    }

    public function render()
    {
        return view('livewire.frontend.auth.login');
    }
}