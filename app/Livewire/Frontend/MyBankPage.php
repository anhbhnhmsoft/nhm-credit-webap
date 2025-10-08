<?php

namespace App\Livewire\Frontend;

use App\Services\AuthService;
use App\Models\Bank;
use Livewire\Component;
use Livewire\WithPagination;

class MyBankPage extends Component
{
    use WithPagination;

    public $banks = [];
    public $showForm = false;
    public $editingAccount = null;
    
    public $bank_id = '';
    public $account_number = '';
    public $account_holder_name = '';

    protected $rules = [
        'bank_id' => 'required|exists:banks,id',
        'account_number' => 'required|string|max:20',
        'account_holder_name' => 'required|string|max:255',
    ];

    protected $messages = [
        'bank_id.required' => 'Vui lòng chọn ngân hàng',
        'bank_id.exists' => 'Ngân hàng không hợp lệ',
        'account_number.required' => 'Vui lòng nhập số tài khoản',
        'account_number.max' => 'Số tài khoản không được quá 20 ký tự',
        'account_holder_name.required' => 'Vui lòng nhập tên chủ tài khoản',
        'account_holder_name.max' => 'Tên chủ tài khoản không được quá 255 ký tự',
    ];

    public function mount()
    {
        $this->banks = Bank::orderBy('name')->get()->toArray();
    }

    public function render()
    {
        $bankAccounts = app(AuthService::class)->getUserBankAccounts(auth()->id());
        
        return view('livewire.frontend.my-bank-page', [
            'bankAccounts' => $bankAccounts
        ]);
    }

    public function showAddForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function showEditForm($accountId)
    {
        $account = collect($this->render()->getData()['bankAccounts'])
            ->firstWhere('id', $accountId);
            
        if ($account) {
            $this->editingAccount = $accountId;
            $this->bank_id = $account['bank_id'];
            $this->account_number = $account['account_number'];
            $this->account_holder_name = $account['account_holder_name'];
            $this->showForm = true;
        }
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editingAccount) {
                app(AuthService::class)->updateBankAccount(
                    $this->editingAccount,
                    auth()->id(),
                    $this->bank_id,
                    $this->account_number,
                    $this->account_holder_name
                );
                session()->flash('success', 'Cập nhật tài khoản ngân hàng thành công');
            } else {
                app(AuthService::class)->createBankAccount(
                    auth()->id(),
                    $this->bank_id,
                    $this->account_number,
                    $this->account_holder_name
                );
                session()->flash('success', 'Thêm tài khoản ngân hàng thành công');
            }

            $this->resetForm();
            $this->showForm = false;
        } catch (\App\Exceptions\ServiceException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function delete($accountId)
    {
        try {
            app(AuthService::class)->deleteBankAccount($accountId, auth()->id());
            session()->flash('success', 'Xóa tài khoản ngân hàng thành công');
        } catch (\App\Exceptions\ServiceException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function setPrimary($accountId)
    {
        try {
            app(AuthService::class)->setPrimaryAccount($accountId, auth()->id());
            session()->flash('success', 'Đặt tài khoản chính thành công');
        } catch (\App\Exceptions\ServiceException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm()
    {
        $this->editingAccount = null;
        $this->bank_id = '';
        $this->account_number = '';
        $this->account_holder_name = '';
        $this->resetErrorBag();
    }
}
