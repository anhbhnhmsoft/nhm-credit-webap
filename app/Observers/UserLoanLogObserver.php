<?php

namespace App\Observers;

use App\Models\UserLoan;
use App\Models\UserLoanLog;

class UserLoanLogObserver
{
    public function updated(UserLoanLog $userLoanLog)
    {
        $this->updateUserLoanTotalPaid($userLoanLog);
    }

    public function created(UserLoanLog $userLoanLog)
    {
        $this->updateUserLoanTotalPaid($userLoanLog);
    }

    private function updateUserLoanTotalPaid(UserLoanLog $userLoanLog): void
    {
        $totalPaidFromLogs = UserLoanLog::where('user_loan_id', $userLoanLog->user_loan_id)
            ->sum('total_paid');

        UserLoan::withoutEvents(function () use ($userLoanLog, $totalPaidFromLogs) {
            UserLoan::where('id', $userLoanLog->user_loan_id)
                ->update(['total_paid_amount' => $totalPaidFromLogs]);
        });
    }
}
