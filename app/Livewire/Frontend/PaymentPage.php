<?php

namespace App\Livewire\Frontend;

use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Models\UserBankAccount;
use App\Models\Config;
use App\Utils\Constants\ConfigName;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaymentPage extends Component
{
    public $loanLogId;
    public $loan;
    public $loanLog;
    public $bankAccount;
    public $message = '';
    public $totalDue = 0;
    public $qrImagePath = '';
    public $bankName = '';
    public $accountName = '';
    public $accountNumber = '';

    public function mount($id)
    {
        $this->loanLogId = $id;
        
        if (!Auth::check()) {
            $sessionUser = session('user');
            if (!$sessionUser) {
                $this->message = 'Bạn cần đăng nhập để xem thông tin thanh toán.';
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
                $this->message = 'Bạn không có quyền truy cập khoản vay này.';
            } else {
                $this->message = 'Không tìm thấy thông tin khoản vay với ID: ' . $this->loanLog->user_loan_id;
            }
            return;
        }

        $this->totalDue = (float) $this->loanLog->principal_due + (float) $this->loanLog->interest_due + (float) $this->loanLog->fee_due;

        $this->bankAccount = UserBankAccount::where('user_id', $userId)->first();

        // Load config từ database
        $qrConfig = Config::where('config_key', ConfigName::QR_IMAGE->value)->first();
        $this->qrImagePath = $qrConfig ? $qrConfig->config_value : 'images/qr-code.png';

        $bankNameConfig = Config::where('config_key', ConfigName::ADMIN_ACCOUNT_NAME_BANK->value)->first();
        $this->bankName = $bankNameConfig ? $bankNameConfig->config_value : 'MB BANK ngân hàng quân đội Việt Nam';

        $accountNameConfig = Config::where('config_key', ConfigName::ADMIN_ACCOUNT_BANK_NAME->value)->first();
        $this->accountName = $accountNameConfig ? $accountNameConfig->config_value : 'JOOTVAY';

        $accountNumberConfig = Config::where('config_key', ConfigName::ADMIN_ACCOUNT_BANK_ACCOUNT->value)->first();
        $this->accountNumber = $accountNumberConfig ? $accountNumberConfig->config_value : 'MB969007392069';
    }


    public function render()
    {
        return view('livewire.frontend.payment-page');
    }
}
