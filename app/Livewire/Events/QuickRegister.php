<?php

namespace App\Livewire\Events;

use App\Services\AuthService;
use App\Utils\Constants\RoleUser;
use Exception;
use Illuminate\Validation\Rule;
use Livewire\Component;

class QuickRegister extends Component
{
    private $authService;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';

    public $isSubmitting = false;
    public $resultStatus = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email'
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^[\d\s\-\+\(\)]+$/',
                'unique:users,phone'
            ],
            'address' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Họ và tên là bắt buộc.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ và tên không được quá 100 ký tự.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.min' => 'Số điện thoại phải có ít nhất 10 số.',
            'phone.max' => 'Số điện thoại không được quá 15 số.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'address.max' => 'Địa chỉ không được quá 255 ký tự.',
        ];
    }

    public function mount()
    {
        // Khởi tạo giá trị mặc định
    }

    public function boot(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => RoleUser::CUSTOMER->value,
            'password' => $this->phone, // Mật khẩu mặc định là số điện thoại
        ];

        try {
            $result = $this->authService->register($data);
            $this->resultStatus = $result['status'];
            
            if ($result['status']) {
                $this->resetForm();
                session()->flash('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.');
            } else {
                session()->flash('error', $result['message'] ?? 'Đã xảy ra lỗi trong quá trình đăng ký.');
            }
        } catch (Exception $e) {
            $this->resultStatus = false;
            session()->flash('error', 'Đã xảy ra lỗi trong quá trình đăng ký. Vui lòng thử lại.');
        }

        $this->isSubmitting = false;
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'email',
            'phone',
            'address',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.events.quick-register');
    }
}
