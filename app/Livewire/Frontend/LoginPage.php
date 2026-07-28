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
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 6 ký tự.',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự.',
        ]);

        $credentials = [
            'phone' => $this->phone,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials)) {
            return redirect()->route('profile');
        } else {
            $this->addError('phone', 'Số điện thoại hoặc mật khẩu không đúng.');
        }
    }

    public function render()
    {
        return view('livewire.frontend.auth.login');
    }
}