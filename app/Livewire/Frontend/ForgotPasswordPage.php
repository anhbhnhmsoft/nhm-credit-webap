<?php

namespace App\Livewire\Frontend;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Quên mật khẩu')]
class ForgotPasswordPage extends Component
{
    public $phone = '';
    public $number_card = '';
    public $new_password = '';
    public $confirm_password = '';
    
    public $step = 1;
    public $user_id = null;
    public $message = '';
    public $error = '';

    protected $rules = [
        'phone' => 'required|string|min:10|max:15',
        'number_card' => 'required|string|min:9|max:12',
        'new_password' => 'required|string|min:6',
        'confirm_password' => 'required|string|same:new_password',
    ];

    protected $messages = [
        'phone.required' => 'Vui lòng nhập số điện thoại',
        'phone.min' => 'Số điện thoại phải có ít nhất 10 số',
        'phone.max' => 'Số điện thoại không được quá 15 số',
        'number_card.required' => 'Vui lòng nhập số thẻ căn cước',
        'number_card.min' => 'Số thẻ căn cước phải có ít nhất 9 số',
        'number_card.max' => 'Số thẻ căn cước không được quá 12 số',
        'new_password.required' => 'Vui lòng nhập mật khẩu mới',
        'new_password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        'confirm_password.required' => 'Vui lòng xác nhận mật khẩu',
        'confirm_password.same' => 'Mật khẩu xác nhận không khớp',
    ];

    public function step1()
    {
        $this->validate([
            'phone' => 'required|string|min:10|max:15',
            'number_card' => 'required|string|min:9|max:12',
        ]);

        try {
            $user = User::where('phone', $this->phone)
                       ->where('number_card', $this->number_card)
                       ->first();

            if (!$user) {
                $this->error = 'Số điện thoại hoặc số thẻ căn cước không chính xác';
                $this->message = '';
                return;
            }

            $this->user_id = $user->id;
            $this->message = 'Xác thực thành công! Vui lòng nhập mật khẩu mới.';
            $this->step = 2;
            $this->error = '';

        } catch (\Exception $e) {
            $this->error = 'Có lỗi xảy ra, vui lòng thử lại: ' . $e->getMessage();
            $this->message = '';
        }
    }

    public function step2()
    {
        $this->validate([
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        try {
            $user = User::find($this->user_id);
            if (!$user) {
                $this->error = 'Không tìm thấy người dùng';
                return;
            }

            $user->password = Hash::make($this->new_password);
            $user->hash_encrypt = Crypt::encryptString($this->new_password);
            $user->save();

            $this->message = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.';
            $this->step = 3;
            $this->error = '';

        } catch (\Exception $e) {
            $this->error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }

    public function resetForm()
    {
        $this->reset([
            'phone', 'number_card', 'new_password', 'confirm_password',
            'step', 'user_id', 'message', 'error'
        ]);
    }

    public function backToStep1()
    {
        $this->step = 1;
        $this->new_password = '';
        $this->confirm_password = '';
        $this->error = '';
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.frontend.forgot-password-page');
    }
}
