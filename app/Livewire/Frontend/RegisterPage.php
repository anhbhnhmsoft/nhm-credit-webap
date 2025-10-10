<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class RegisterPage extends Component
{
    public string $phone = '';
    public string $fullName = '';
    public string $email = '';
    public string $password = '';
    public string $confirmPassword = '';

    public function submit()
    {
        $this->validate([
            'phone' => 'required|string|max:20|unique:users,phone',
            'fullName' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|same:password',
        ], [
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
            'email.unique' => 'Email này đã được đăng ký.',
            'confirmPassword.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $user = User::create([
            'phone' => $this->phone,
            'name' => $this->fullName,
            'email' => $this->email ?: null,
            'password' => Hash::make($this->password),
            'role' => RoleUser::CUSTOMER->value,
        ]);

        Auth::login($user);
        
        session()->flash('success', 'Đăng ký thành công!');
        return redirect()->route('profile');
    }

    public function render()
    {
        return view('livewire.frontend.auth.register');
    }
}
