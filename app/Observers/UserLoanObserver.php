<?php

namespace App\Observers;

use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Utils\Constants\LoanStatus;
use App\Utils\Constants\LoanLogStatus;
use Illuminate\Support\Facades\DB;

class UserLoanObserver
{
    public function updated(UserLoan $userLoan)
    {
        if ($userLoan->isDirty('status') && $userLoan->status == LoanStatus::COMPLETED->value) {
            UserLoanLog::where('user_loan_id', $userLoan->id)
                ->update([
                    'status' => LoanLogStatus::PAID->value,
                    'actual_due_date' => now(),
                    'total_paid' => DB::raw('principal_due + interest_due + fee_due')
                ]);
        }

        if ($userLoan->isDirty('due_date') && $userLoan->due_date) {
            UserLoanLog::where('user_loan_id', $userLoan->id)
                ->update(['due_date' => $userLoan->due_date]);
        }

        $this->updateTotalPaidAmount($userLoan);
    }

    private function updateTotalPaidAmount(UserLoan $userLoan): void
    {
        $totalPaidFromLogs = UserLoanLog::where('user_loan_id', $userLoan->id)
            ->sum('total_paid');

        if ($userLoan->total_paid_amount != $totalPaidFromLogs) {
            UserLoan::withoutEvents(function () use ($userLoan, $totalPaidFromLogs) {
                $userLoan->update(['total_paid_amount' => $totalPaidFromLogs]);
            });
        }
    }
}