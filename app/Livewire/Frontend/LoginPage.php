<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginPage extends Component
{
    public string $phone = '';
    public string $password = '';

    public function submit()
    {
        $this->validate([
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        $credentials = [
            'phone' => $this->phone,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials)) {
            return redirect()->route('profile');
        } else {
            session()->flash('error', 'Số điện thoại hoặc mật khẩu không đúng.');
        }
    }

    public function render()
    {
        return view('livewire.frontend.auth.login');
    }
}