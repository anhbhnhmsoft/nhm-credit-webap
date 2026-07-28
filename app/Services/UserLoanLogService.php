<?php

namespace App\Services;

use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Utils\Constants\LoanLogStatus;
use App\Utils\Constants\LoanStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserLoanLogService
{
    

    public function generateLogsForLoan(UserLoan $loan): array
    {
        $termMonths = $this->parseTermMonths($loan->term_months);
        if ($termMonths <= 0) {
            return [
                'status' => false,
                'message' => 'Kỳ hạn vay không hợp lệ',
                'created' => 0,
            ];
        }
        if ($loan->status !== LoanStatus::ACTIVE->value || !$loan->start_date) {
            return [
                'status' => false,
                'message' => 'Khoản vay không hợp lệ để tạo log',
                'created' => 0,
            ];
        }

        $existingLog = UserLoanLog::query()
            ->where('user_loan_id', $loan->id)
            ->first();

        if ($existingLog) {
            return [
                'status' => false,
                'message' => 'Khoản vay đã có log rồi',
                'created' => 0,
            ];
        }

        $principalAmount = (float) ($loan->total_due_amount);
        $interestAmount = (float) $loan->interest_rate_year;
        $serviceFee = (float) $loan->service_fee_amount;

        $dueDate = $loan->due_date ? Carbon::parse($loan->due_date) : null;

        DB::transaction(function () use ($loan, $principalAmount, $interestAmount, $serviceFee, $termMonths, $dueDate, &$created) {
            UserLoanLog::create([
                'user_loan_id' => $loan->id,
                'installment_no' => $termMonths,
                'due_date' => $dueDate,
                'actual_due_date' => null,
                'principal_due' => $principalAmount,
                'interest_due' => $interestAmount,
                'fee_due' => $serviceFee,
                'total_paid' => 0,
                'status' => LoanLogStatus::PENDING->value,
            ]);
            $created++;
        });

        return [
            'status' => true,
            'message' => "Đã tạo log trả nợ cho khoản vay #{$loan->id}",
            'created' => $created,
        ];
    }

    public function getUserLoanLogsDue(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return UserLoanLog::query()
            ->with('userLoan')
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

    public function getUserLoanLogsPaid(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return UserLoanLog::query()
            ->with('userLoan')
            ->whereHas('userLoan', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('status', LoanLogStatus::PAID->value)
            ->orderBy('actual_due_date', 'desc')
            ->get();
    }

    private function parseTermMonths($termMonths): int
    {
        if (empty($termMonths)) {
            return 0;
        }
        
        preg_match('/(\d+)/', (string) $termMonths, $matches);
        
        if (empty($matches[1])) {
            return 0;
        }
        
        return (int) $matches[1];
    }
}


