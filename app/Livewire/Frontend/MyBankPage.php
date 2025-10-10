<?php

namespace App\Livewire\Frontend;

use App\Exceptions\ServiceException;
use App\Services\AuthService;
use App\Services\BankAccountService;
use App\Services\BankService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MyBankPage extends Component
{
    use WithPagination;

    public $banks = [];
    public $showForm = false;
    public $editingAccount = null;
    
    public $bank_id = '';
    public $account_number = '';
    public $account_name = '';
    public $message = '';
    
    protected $rules = [
        'bank_id' => 'required|exists:banks,id',
        'account_number' => 'required|string|max:20',
        'account_name' => 'required|string|max:255',
    ];

    protected $messages = [
        'bank_id.required' => 'Vui lòng chọn ngân hàng',
        'bank_id.exists' => 'Ngân hàng không hợp lệ',
        'account_number.required' => 'Vui lòng nhập số tài khoản',
        'account_number.max' => 'Số tài khoản không được quá 20 ký tự',
        'account_name.required' => 'Vui lòng nhập tên chủ tài khoản',
        'account_name.max' => 'Tên chủ tài khoản không được quá 255 ký tự',
    ];

    protected AuthService $authService;
    protected BankAccountService $bankAccountService;
    protected BankService $bankService;

    public function boot(AuthService $authService, BankService $bankService, BankAccountService $bankAccountService): void
    {
        $this->authService = $authService;
        $this->bankService = $bankService;
        $this->bankAccountService = $bankAccountService;
    }

    public function mount()
    {
        $this->banks = $this->bankService->list()->toArray();
    }

    public function render()
    {
        if (!Auth::check()) {
            $this->message = 'Bạn cần đăng nhập để xem và quản lý tài khoản ngân hàng.';
            return view('livewire.frontend.my-bank-page', [
                'bankAccount' => null,
                'message' => $this->message,
            ]);
        }

        $accounts = $this->bankAccountService->getUserBankAccounts(Auth::id()); // array
        $bankAccount = $accounts[0] ?? null;

        $this->message = '';
        return view('livewire.frontend.my-bank-page', [
            'bankAccount' => $bankAccount,
            'message' => $this->message,
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
            $this->account_name = $account['account_name'];
            $this->showForm = true;
        }
    }

    public function save()
    {
        $this->validate();
        $this->showForm = false;
        try {
            if ($this->editingAccount) {
                $this->bankAccountService->updateBankAccount(
                    $this->editingAccount,
                    Auth::id(),
                    $this->bank_id,
                    $this->account_number,
                    $this->account_name
                );
                session()->flash('success', 'Cập nhật tài khoản ngân hàng thành công');
            } else {
                $this->bankAccountService->createBankAccount(
                    Auth::id(),
                    $this->bank_id,
                    $this->account_number,
                    $this->account_name
                );
                session()->flash('success', 'Thêm tài khoản ngân hàng thành công');
            }

            $this->resetForm();
            $this->showForm = false;
        } catch (ServiceException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function delete($accountId)
    {
        try {
            $this->bankAccountService->deleteBankAccount($accountId, Auth::id());
            session()->flash('success', 'Xóa tài khoản ngân hàng thành công');
        } catch (ServiceException $e) {
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
        $this->account_name = '';
        $this->resetErrorBag();
    }
}
