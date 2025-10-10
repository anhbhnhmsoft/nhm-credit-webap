<?php

namespace App\Services;

use App\Models\UserLoan;
use App\Models\Payment;
use App\Models\LoanPackage;
use App\Utils\Constants\PaymentStatus;
use App\Utils\Constants\PaymentDirection;
use App\Utils\Constants\LoanStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function getDisbursedAmountReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::OUT->value);

        $groupBy = $this->getGroupByClause($period);

        $disbursed = $query
            ->selectRaw("
                {$groupBy} as period,
                SUM(amount) as total_disbursed,
                COUNT(*) as disbursement_count
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'data' => $disbursed,
            'total_disbursed' => $disbursed->sum('total_disbursed'),
            'total_disbursements' => $disbursed->sum('disbursement_count'),
            'average_disbursement' => $disbursed->avg('total_disbursed'),
        ];
    }

    public function getCollectedAmountReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value); // Chỉ tính thu vào

        $groupBy = $this->getGroupByClause($period);

        $collected = $query
            ->selectRaw("
                {$groupBy} as period,
                SUM(amount) as total_collected,
                COUNT(*) as collection_count
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();


        return [
            'data' => $collected,
            'total_collected' => $collected->sum('total_collected'),
            'total_collections' => $collected->sum('collection_count'),
            'average_collection' => $collected->avg('total_collected'),
        ];
    }

    public function getDashboardReport(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $todayRevenue = Payment::whereDate('created_at', $today)
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value)
            ->sum('amount');

        $thisMonthRevenue = Payment::whereBetween('created_at', [$thisMonth, Carbon::now()])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value)
            ->sum('amount');

        $lastMonthRevenue = Payment::whereBetween('created_at', [$lastMonth, $thisMonth])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value)
            ->sum('amount');

        $totalLoanAmount = UserLoan::whereIn('status', [
            LoanStatus::ACTIVE->value, 
            LoanStatus::OVERDUE->value,
            LoanStatus::COMPLETED->value
        ])->sum('principal_amount');

        $activeLoans = UserLoan::whereIn('status', [LoanStatus::APPROVED->value, LoanStatus::ACTIVE->value, LoanStatus::OVERDUE->value])->count();

        $totalLoans = UserLoan::where('status', LoanStatus::APPROVED->value)->count();
        $overdueLoans = UserLoan::where('status', LoanStatus::OVERDUE->value)->count();
        $overdueRate = $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;

        $newCustomers = UserLoan::whereBetween('created_at', [$thisMonth, Carbon::now()])
            ->distinct('user_id')
            ->count('user_id');

        return [
            'today_revenue' => $todayRevenue,
            'this_month_revenue' => $thisMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'revenue_growth' => $lastMonthRevenue > 0 ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0,
            'total_loan_amount' => $totalLoanAmount,
            'active_loans' => $activeLoans,
            'overdue_rate' => $overdueRate,
            'new_customers' => $newCustomers,
        ];
    }

    private function getGroupByClause($period): string
    {
        return match ($period) {
            'daily' => "DATE(created_at)",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
            'quarterly' => "CONCAT(YEAR(created_at), '-Q', QUARTER(created_at))",
            'yearly' => "YEAR(created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };
    }
}
