<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
#[Title('Đăng nhập')]
class LoginUnifiedPage extends Component
{
    public string $tab = 'otp';
    public string $phone = '';
    public string $password = '';

    public function switchTab(string $tab): void
    {
        $this->tab = in_array($tab, ['password', 'otp'], true) ? $tab : 'password';
    }

    public function submitPassword()
    {
        $this->validate([
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 6 ký tự.',
        ]);

        if (Auth::attempt(['phone' => $this->phone, 'password' => $this->password])) {
            return redirect()->route('profile');
        }

        $this->addError('phone', 'Số điện thoại hoặc mật khẩu không đúng.');
    }

    public function render()
    {
        return view('livewire.frontend.auth.login-unified');
    }
}
