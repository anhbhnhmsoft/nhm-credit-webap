<?php

namespace App\Services;

use App\Models\UserLoan;
use Illuminate\Database\Eloquent\Collection;

class UserLoanService
{
    public function getUserLoans(int $userId): Collection
    {
        return UserLoan::query()
            ->where('user_id', $userId)
            ->get();
    }
}