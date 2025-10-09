<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Utils\Constants\PaymentDirection;
use App\Utils\Constants\PaymentStatus;
use App\Utils\Constants\LoanLogStatus;
use App\Utils\Helper;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createDisbursementPayment(UserLoan $userLoan, float $amount, ?string $description = null): Payment
    {
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'user_id' => $userLoan->user_id,
                'user_loan_id' => $userLoan->id,
                'user_loan_log_id' => null,
                'transaction_code' => 'DISB' . Helper::getTimestampAsId(),
                'amount' => $amount,
                'direction' => PaymentDirection::OUT->value, // Chi ra cho user
                'status' => PaymentStatus::SUCCESS->value,
                'description' => $description ?? "Giải ngân khoản vay #{$userLoan->id}",
            ]);


            DB::commit();
            return $payment;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createLoanPayment(UserLoanLog $userLoanLog, float $amount, ?string $description = null): Payment
    {
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'user_id' => $userLoanLog->userLoan->user_id,
                'user_loan_id' => $userLoanLog->user_loan_id,
                'user_loan_log_id' => $userLoanLog->id,
                'transaction_code' => 'PAY' . Helper::getTimestampAsId(),
                'amount' => $amount,
                'direction' => PaymentDirection::IN->value, // Thu vào từ user
                'status' => PaymentStatus::SUCCESS->value,
                'description' => $description ?? "Thanh toán kỳ {$userLoanLog->installment_no}",
            ]);

            $userLoanLog->increment('total_paid', $amount);

            $userLoanLog->userLoan->increment('total_paid_amount', $amount);

            $this->updateLoanLogStatus($userLoanLog);

            $this->updateLoanStatus($userLoanLog->userLoan);

            DB::commit();
            return $payment;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateLoanLogStatus(UserLoanLog $userLoanLog): void
    {
        $totalDue = $userLoanLog->principal_due + $userLoanLog->interest_due + $userLoanLog->fee_due;
        $totalPaid = $userLoanLog->total_paid;

        if ($totalPaid >= $totalDue) {
            $userLoanLog->update([
                'status' => LoanLogStatus::PAID->value,
                'actual_due_date' => now(),
            ]);
        } elseif ($totalPaid > 0) {
            $userLoanLog->update([
                'status' => LoanLogStatus::PARTIAL->value,
            ]);
        }
    }

    private function updateLoanStatus(UserLoan $userLoan): void
    {
        $totalDueAmount = $userLoan->total_due_amount;
        $totalPaidAmount = $userLoan->total_paid_amount;

        if ($totalPaidAmount >= $totalDueAmount) {
            $userLoan->update(['status' => \App\Utils\Constants\LoanStatus::COMPLETED->value]);
        }
    }

    public function createRefundPayment(int $userId, float $amount, ?string $description = null): Payment
    {
        return Payment::create([
            'user_id' => $userId,
            'user_loan_id' => null,
            'user_loan_log_id' => null,
            'transaction_code' => 'REF' . Helper::getTimestampAsId(),
            'amount' => $amount,
            'direction' => PaymentDirection::OUT->value,
            'status' => PaymentStatus::SUCCESS->value,
            'description' => $description ?? 'Hoàn tiền',
        ]);
    }

    public function updatePaymentStatus(Payment $payment, PaymentStatus $status, ?string $description = null): bool
    {
        return $payment->update([
            'status' => $status->value,
            'description' => $description ?? $payment->description,
        ]);
    }

    public function getPaymentsByUser(int $userId, ?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Payment::where('user_id', $userId)
            ->with(['user', 'userLoan', 'userLoanLog'])
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getPaymentStats(\Carbon\Carbon $from, \Carbon\Carbon $to): array
    {
        $payments = Payment::whereBetween('created_at', [$from, $to])
            ->selectRaw('
                direction,
                status,
                COUNT(*) as count,
                SUM(amount) as total_amount
            ')
            ->groupBy('direction', 'status')
            ->get();

        $stats = [
            'total_transactions' => $payments->sum('count'),
            'total_amount' => $payments->sum('total_amount'),
            'incoming' => [
                'count' => $payments->where('direction', PaymentDirection::IN->value)->sum('count'),
                'amount' => $payments->where('direction', PaymentDirection::IN->value)->sum('total_amount'),
            ],
            'outgoing' => [
                'count' => $payments->where('direction', PaymentDirection::OUT->value)->sum('count'),
                'amount' => $payments->where('direction', PaymentDirection::OUT->value)->sum('total_amount'),
            ],
        ];

        return $stats;
    }
}
