<?php

namespace App\Livewire\Frontend;

use App\Services\UserLoanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LoanApplication extends Component
{
    use WithPagination;

    public $tab = 'pending';
    public $loans = [];
    public $loanLogs = [];
    public $message = '';
    protected $rules = [
        'tab' => 'required|in:pending,approved,paid',
    ];

    protected $messages = [
        'tab.required' => 'Vui lòng chọn tab',
        'tab.in' => 'Tab không hợp lệ',
    ];

    protected UserLoanService $userLoanService;

    public function boot(UserLoanService $userLoanService): void
    {
        $this->userLoanService = $userLoanService;
    }

    public function changeTab(string $tab)
    {
        $this->tab = $tab;
        $this->loadLoansByTab();
    }

    public function mount()
    {
        if (Auth::check()) {
            $this->loadLoansByTab();
            $this->message = '';
        } else {
            $this->loans = collect([]);
            $this->message = 'Bạn cần đăng nhập để xem thông tin đơn vay.';
        }
    }

    private function loadLoansByTab()
    {
        if (!Auth::check()) {
            $this->loans = collect([]);
            $this->loanLogs = collect([]);
            return;
        }

        $allLoans = $this->userLoanService->getUserLoans(Auth::id());
        
        switch ($this->tab) {
            case 'pending':
                $this->loanLogs = $this->userLoanService->getUserLoanLogsDue(Auth::id());
                $this->loans = collect([]);
                break;
                
            case 'approved':
                $this->loans = $allLoans->filter(function ($loan) {
                    return in_array($loan->status, [1, 2]); // PENDING, APPROVED
                });
                $this->loanLogs = collect([]);
                break;
                
            case 'paid':
                $this->loans = $allLoans->filter(function ($loan) {
                    return $loan->status == 5; // COMPLETED
                });
                $this->loanLogs = collect([]);
                break;
                
            default:
                $this->loans = $allLoans;
                $this->loanLogs = collect([]);
        }
    }

    public function render()
    {
        return view('livewire.frontend.loan-application');
    }
}
