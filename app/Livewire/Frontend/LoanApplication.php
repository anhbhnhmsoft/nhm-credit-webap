<?php

namespace App\Livewire\Frontend;

use App\Utils\Constants\LoanLogStatus;
use App\Services\UserLoanService;
use App\Services\UserLoanLogService;
use App\Utils\Constants\LoanStatus;
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
    protected UserLoanLogService $userLoanLogService;

    public function boot(UserLoanService $userLoanService, UserLoanLogService $userLoanLogService): void
    {
        $this->userLoanService = $userLoanService;
        $this->userLoanLogService = $userLoanLogService;
    }

    public function changeTab(string $tab)
    {
        $this->tab = $tab;
        $this->loadLoansByTab();
    }

    public function mount()
    {
        $requestedTab = request()->query('tab');
        if (in_array($requestedTab, ['pending', 'approved', 'paid'], true)) {
            $this->tab = $requestedTab;
        }

        if (Auth::check() || session('user')) {
            $this->loadLoansByTab();
            $this->message = '';
        } else {
            $this->loans = collect([]);
            $this->message = 'Bạn cần đăng nhập để xem thông tin đơn vay.';
        }
    }

    private function loadLoansByTab()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->loans = collect([]);
            $this->loanLogs = collect([]);
            return;
        }

        $allLoans = $this->userLoanService->getUserLoans($userId);
        
        switch ($this->tab) {
            case 'pending':
                $this->loanLogs = $this->userLoanLogService->getUserLoanLogsDue($userId);
                $this->loans = collect([]);
                break;
                
            case 'approved':
                $this->loans = $allLoans->filter(function ($loan) {
                    return in_array($loan->status, [LoanStatus::PENDING->value, LoanStatus::APPROVED->value]);
                });
                $this->loanLogs = collect([]);
                break;
                
            case 'paid':
                $this->loanLogs = $this->userLoanLogService->getUserLoanLogsPaid($userId);
                $this->loans = collect([]);
                break;
                
            default:
                $this->loans = $allLoans;
                $this->loanLogs = collect([]);
        }
    }

    private function getUserId()
    {
        if (Auth::check()) {
            return Auth::id();
        }
        
        $sessionUser = session('user');
        return $sessionUser ? $sessionUser->id : null;
    }

    public function viewLoanDetail($loanId)
    {
        return redirect()->route('loan-detail', ['id' => $loanId]);
    }

    public function render()
    {
        return view('livewire.frontend.loan-application');
    }
}
