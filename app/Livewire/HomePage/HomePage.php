<?php

namespace App\Livewire\HomePage;

use App\Models\LoanPackage;
use App\Models\UserLoan;
use App\Utils\Constants\LoanStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class HomePage extends Component
{
    public $amount = 2000000;
    public int $days = 180;

    public array $quickAmounts = [2000000, 5000000, 10000000, 15000000, 20000000];
    
    public $activeLoanPackage = null;
    public $selectedTermMonths = 6;

    public function setAmount(int $value): void
    {
        $this->amount = $value;
    }

    public function setDays(int $days): void
    {
        $this->days = $days;
    }

    public function setSelectedTerm(int $months): void
    {
        $this->selectedTermMonths = $months;
    }

    public function updatedAmount(): void
    {
        if ($this->amount < 0) {
            $this->amount = 0;
        }
    }

    public function mount(): void
    {
        $this->activeLoanPackage = LoanPackage::whereJsonContains('config_loans->active', true)->first();
        
        if ($this->activeLoanPackage) {
            $config = $this->activeLoanPackage->config_loans;
            $this->amount = data_get($config, 'min_amount', 2000000);
            $minAmount = data_get($config, 'min_amount', 2000000);
            $maxAmount = data_get($config, 'max_amount', 20000000);
            $range = $maxAmount - $minAmount;
            
            $minK = $minAmount / 1000;
            $maxK = $maxAmount / 1000;
            
            $this->quickAmounts = [
                floor($minK / 1000) * 1000 * 1000,
                floor(($minK + ($maxK - $minK) * 0.25) / 1000) * 1000 * 1000,
                floor(($minK + ($maxK - $minK) * 0.5) / 1000) * 1000 * 1000,
                floor(($minK + ($maxK - $minK) * 0.75) / 1000) * 1000 * 1000,
                floor($maxK / 1000) * 1000 * 1000
            ];
            
            $termMonths = data_get($config, 'term_month', []);
            if (is_array($termMonths) && !empty($termMonths)) {
                $this->selectedTermMonths = $termMonths[0];
                $this->days = $termMonths[0] * 30;
            }
        }
    }

    public function submitLoanRequest($amount = null)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Bạn cần đăng nhập để gửi yêu cầu vay.');
            return redirect()->route('register');
        }

        if (!$this->activeLoanPackage) {
            session()->flash('error', 'Hiện tại không có gói vay nào đang hoạt động.');
            return;
        }

        $this->amount = (int) ($amount ?? $this->amount);
        
        $config = $this->activeLoanPackage->config_loans;
        $minAmount = data_get($config, 'min_amount', 0);
        $maxAmount = data_get($config, 'max_amount', 0);
        
        if ($this->amount < $minAmount || $this->amount > $maxAmount) {
            session()->flash('error', "Số tiền vay phải từ " . number_format($minAmount) . " đến " . number_format($maxAmount) . " VNĐ.");
            return;
        }

        $termMonths = data_get($config, 'term_month', []);
        if (!in_array($this->selectedTermMonths, $termMonths)) {
            session()->flash('error', 'Kỳ hạn vay không hợp lệ.');
            return;
        }

        try {
            $userLoan = UserLoan::create([
                'user_id' => Auth::id(),
                'loan_package_id' => $this->activeLoanPackage->id,
                'principal_amount' => $this->amount,
                'term_months' => $this->selectedTermMonths,
                'interest_rate_year' => data_get($config, 'interest_rate', 0),
                'service_fee_amount' => 0,
                'disbursed_amount' => 0,
                'total_due_amount' => 0,
                'monthly_payment' => 0,
                'total_paid_amount' => 0,
                'status' => LoanStatus::PENDING->value,
            ]);

            session()->flash('success', 'Yêu cầu vay đã được gửi thành công! Chúng tôi sẽ xem xét và phản hồi trong thời gian sớm nhất.');
            return redirect()->route('loan-application');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra khi gửi yêu cầu vay. Vui lòng thử lại.');
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.frontend.home-page');
    }
}


