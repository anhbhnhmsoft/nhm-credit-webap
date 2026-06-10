<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\PaymentDirection;
use Illuminate\Support\Facades\DB;

class UserLoanApprovalService
{
    public function approve(UserLoan $loan, string $mode, ?string $startDate = null, ?float $disbursedAmount = null): void
    {
        DB::transaction(function () use ($loan, $mode, $startDate, $disbursedAmount): void {
            $isDisburse = $mode === 'approve_and_disburse';
            $disbursed = (float) ($disbursedAmount ?? 0);

            if ($isDisburse) {
                if ($disbursed <= 0) {
                    throw new \InvalidArgumentException('Số tiền giải ngân phải lớn hơn 0.');
                }

                if ($disbursed > (float) $loan->principal_amount) {
                    throw new \InvalidArgumentException('Số tiền giải ngân không được vượt quá số tiền vay.');
                }
            }

            $status = ($isDisburse && $disbursed > 0)
                ? LoanStatus::ACTIVE->value
                : LoanStatus::APPROVED->value;

            $updates = [
                'status' => $status,
                'reject_reason' => null,
            ];

            if ($status === LoanStatus::ACTIVE->value) {
                $updates['start_date'] = $startDate ?: now();
                $updates['disbursed_amount'] = $disbursed;
                $start = $updates['start_date'] instanceof \Carbon\Carbon
                    ? $updates['start_date']->copy()
                    : \Carbon\Carbon::parse($updates['start_date']);
                $updates['due_date'] = $start->copy()->addDays((int) $loan->term_months);
            }

            $loan->update($updates);

            if ($status !== LoanStatus::ACTIVE->value) {
                return;
            }

            $payment = Payment::query()
                ->where('user_loan_id', $loan->id)
                ->where('direction', PaymentDirection::OUT->value)
                ->first();

            if ($payment) {
                $payment->update([
                    'amount' => $disbursed,
                    'description' => "Giải ngân khoản vay #{$loan->id} - " . number_format($disbursed) . " VNĐ",
                ]);
            } else {
                app(PaymentService::class)->createDisbursementPayment(
                    $loan,
                    $disbursed,
                    "Giải ngân khoản vay #{$loan->id} - " . number_format($disbursed) . " VNĐ"
                );
            }

            if (!UserLoanLog::query()->where('user_loan_id', $loan->id)->exists()) {
                app(UserLoanLogService::class)->generateLogsForLoan($loan);
            }
        });
    }

    public function reject(UserLoan $loan, string $reason): void
    {
        $loan->update([
            'status' => LoanStatus::REJECTED->value,
            'reject_reason' => $reason,
        ]);
    }
}
