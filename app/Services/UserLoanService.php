<?php

namespace App\Services;

use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Utils\Constants\LoanLogStatus;
use Illuminate\Database\Eloquent\Collection;

class UserLoanService
{
    public function getUserLoans(int $userId): Collection
    {
        return UserLoan::query()
            ->where('user_id', $userId)
            ->get();
    }

    public function getUserLoanLogsDue(int $userId): Collection
    {
        return UserLoanLog::query()
            ->whereHas('userLoan', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereIn('status', [
                LoanLogStatus::PENDING->value,
                LoanLogStatus::PARTIAL->value,
                LoanLogStatus::OVERDUE->value,
            ])
            ->orderBy('due_date')
            ->get();
    }
}