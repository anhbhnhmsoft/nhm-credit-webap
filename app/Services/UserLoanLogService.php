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
    public function generateDailyLogs(): array
    {
        $created = 0;

        $loans = UserLoan::query()
            ->where('status', '=',  LoanStatus::ACTIVE->value)
            ->whereNotNull('start_date')
            ->get();

        foreach ($loans as $loan) {
            $termMonths = (int) $loan->term_months;
            if ($termMonths <= 0) {
                continue;
            }

            $interestTermPercent = (float) $loan->interest_rate_year; // % cho toàn kỳ hạn
            $monthlyPayment = app(LoanCalculationService::class)->calcMonthlyPayment(
                (float) ($loan->disbursed_amount ?: $loan->principal_amount),
                0,
                $termMonths,
                $interestTermPercent
            );

            $outstanding = (float) ($loan->disbursed_amount ?: $loan->principal_amount);

            $existingCount = UserLoanLog::query()
                ->where('user_loan_id', $loan->id)
                ->count();

            for ($k = $existingCount + 1; $k <= $termMonths; $k++) {
                $interestDue = round((($outstanding) * ($interestTermPercent / 100)) / $termMonths, 2);
                $principalDue = round($monthlyPayment - $interestDue, 2);

                if ($k === $termMonths) {
                    $principalDue = round($outstanding, 2);
                    $monthlyDue = $principalDue + $interestDue;
                }

                $dueDate = ($loan->start_date instanceof Carbon ? $loan->start_date->copy() : Carbon::parse($loan->start_date))
                    ->addMonths($k);

                $exists = UserLoanLog::query()
                    ->where('user_loan_id', $loan->id)
                    ->where('installment_no', $k)
                    ->exists();

                if ($exists) {
                    $outstanding = max($outstanding - $principalDue, 0);
                    continue;
                }

                DB::transaction(function () use ($loan, $k, $dueDate, $principalDue, $interestDue, &$created) {
                    UserLoanLog::create([
                        'user_loan_id' => $loan->id,
                        'installment_no' => $k,
                        'due_date' => $dueDate,
                        'actual_due_date' => null,
                        'principal_due' => $principalDue,
                        'interest_due' => $interestDue,
                        'fee_due' => 0,
                        'total_paid' => 0,
                        'status' => LoanLogStatus::PENDING->value,
                    ]);
                    $created++;
                });

                $outstanding = max($outstanding - $principalDue, 0);
            }
        }

        return [
            'status' => true,
            'message' => "Đã tạo {$created} kỳ trả nợ mới",
            'created' => $created,
        ];
    }

    public function generateLogsForLoan(UserLoan $loan): array
    {
        $created = 0;

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
        $interestAmount = (float) $loan->interest_rate_year; // Phí quá hạn
        $serviceFee = (float) $loan->service_fee_amount;
        $totalDue = $principalAmount + $interestAmount + $serviceFee;

        $dueDate = $loan->due_date ? Carbon::parse($loan->due_date) : null;

        DB::transaction(function () use ($loan, $principalAmount, $interestAmount, $serviceFee, $totalDue, $dueDate, &$created) {
            UserLoanLog::create([
                'user_loan_id' => $loan->id,
                'installment_no' => 1,
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
}


