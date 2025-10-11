<?php

namespace App\Livewire\Frontend;

use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Models\UserBankAccount;
use App\Services\UserLoanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanDetail extends Component
{
    public $loanId;
    public $loan;
    public $loanLog;
    public $bankAccount;
    public $message = '';

    protected UserLoanService $userLoanService;

    public function boot(UserLoanService $userLoanService): void
    {
        $this->userLoanService = $userLoanService;
    }

    public function mount($id)
    {
        $this->loanId = $id;
        
        if (!Auth::check()) {
            $sessionUser = session('user');
            if (!$sessionUser) {
                $this->message = 'Bạn cần đăng nhập để xem thông tin chi tiết.';
                return;
            }
            $userId = $sessionUser->id;
        } else {
            $userId = Auth::id();
        }
        
        $this->loanLog = UserLoanLog::where('id', $id)->first();
        
        if (!$this->loanLog) {
            $this->message = 'Không tìm thấy kỳ trả nợ với ID: ' . $id;
            return;
        }

        $this->loan = UserLoan::with('user')
            ->where('id', $this->loanLog->user_loan_id)
            ->where('user_id', $userId)
            ->first();

        if (!$this->loan) {
            $otherUserLoan = UserLoan::where('id', $this->loanLog->user_loan_id)->first();
            if ($otherUserLoan) {
                $this->message = 'Bạn không có quyền truy cập khoản vay này. User ID hiện tại: ' . $userId . ', Loan thuộc về User ID: ' . $otherUserLoan->user_id;
            } else {
                $this->message = 'Không tìm thấy thông tin khoản vay với ID: ' . $this->loanLog->user_loan_id;
            }
            return;
        }

        $this->bankAccount = UserBankAccount::where('user_id', $userId)->first();
    }

    public function payNow()
    {
        return redirect()->route('payment', ['id' => $this->loanLog->id]);
    }

    public function render()
    {
        return view('livewire.frontend.loan-detail');
    }
}
