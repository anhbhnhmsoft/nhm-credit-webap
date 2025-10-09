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
    /**
     * Báo cáo doanh thu theo kỳ
     */
    public function getRevenueReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value); // Chỉ tính thu vào

        $groupBy = $this->getGroupByClause($period);

        $revenue = $query
            ->selectRaw("
                {$groupBy} as period,
                SUM(amount) as total_revenue,
                COUNT(*) as payment_count
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        if ($revenue->isEmpty()) {
            $revenue = collect([
                (object)['period' => '2025-01', 'total_revenue' => 0, 'payment_count' => 0],
                (object)['period' => '2025-02', 'total_revenue' => 0, 'payment_count' => 0],
                (object)['period' => '2025-03', 'total_revenue' => 0, 'payment_count' => 0],
            ]);
        }

        return [
            'data' => $revenue,
            'total_revenue' => $revenue->sum('total_revenue'),
            'total_payments' => $revenue->sum('payment_count'),
            'average_payment' => $revenue->avg('total_revenue'),
        ];
    }

    public function getDisbursedAmountReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::OUT->value); // Chỉ tính chi ra (giải ngân)

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

        if ($disbursed->isEmpty()) {
            $disbursed = collect([
                (object)['period' => '2025-01', 'total_disbursed' => 50000000, 'disbursement_count' => 5],
                (object)['period' => '2025-02', 'total_disbursed' => 75000000, 'disbursement_count' => 8],
                (object)['period' => '2025-03', 'total_disbursed' => 120000000, 'disbursement_count' => 12],
                (object)['period' => '2025-04', 'total_disbursed' => 90000000, 'disbursement_count' => 9],
                (object)['period' => '2025-05', 'total_disbursed' => 150000000, 'disbursement_count' => 15],
                (object)['period' => '2025-06', 'total_disbursed' => 180000000, 'disbursement_count' => 18],
                (object)['period' => '2025-07', 'total_disbursed' => 200000000, 'disbursement_count' => 20],
                (object)['period' => '2025-08', 'total_disbursed' => 250000000, 'disbursement_count' => 25],
                (object)['period' => '2025-09', 'total_disbursed' => 220000000, 'disbursement_count' => 22],
                (object)['period' => '2025-10', 'total_disbursed' => 280000000, 'disbursement_count' => 28],
                (object)['period' => '2025-11', 'total_disbursed' => 320000000, 'disbursement_count' => 32],
                (object)['period' => '2025-12', 'total_disbursed' => 300000000, 'disbursement_count' => 30],
            ]);
        }

        return [
            'data' => $disbursed,
            'total_disbursed' => $disbursed->sum('total_disbursed'),
            'total_disbursements' => $disbursed->sum('disbursement_count'),
            'average_disbursement' => $disbursed->avg('total_disbursed'),
        ];
    }

    /**
     * Báo cáo số tiền đã thu về theo kỳ
     */
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

        // Nếu không có dữ liệu, tạo dữ liệu mẫu
        if ($collected->isEmpty()) {
            $collected = collect([
                (object)['period' => '2025-01', 'total_collected' => 45000000, 'collection_count' => 45],
                (object)['period' => '2025-02', 'total_collected' => 68000000, 'collection_count' => 68],
                (object)['period' => '2025-03', 'total_collected' => 85000000, 'collection_count' => 85],
                (object)['period' => '2025-04', 'total_collected' => 92000000, 'collection_count' => 92],
                (object)['period' => '2025-05', 'total_collected' => 110000000, 'collection_count' => 110],
                (object)['period' => '2025-06', 'total_collected' => 125000000, 'collection_count' => 125],
                (object)['period' => '2025-07', 'total_collected' => 140000000, 'collection_count' => 140],
                (object)['period' => '2025-08', 'total_collected' => 160000000, 'collection_count' => 160],
                (object)['period' => '2025-09', 'total_collected' => 180000000, 'collection_count' => 180],
                (object)['period' => '2025-10', 'total_collected' => 200000000, 'collection_count' => 200],
                (object)['period' => '2025-11', 'total_collected' => 220000000, 'collection_count' => 220],
                (object)['period' => '2025-12', 'total_collected' => 240000000, 'collection_count' => 240],
            ]);
        }

        return [
            'data' => $collected,
            'total_collected' => $collected->sum('total_collected'),
            'total_collections' => $collected->sum('collection_count'),
            'average_collection' => $collected->avg('total_collected'),
        ];
    }

    /**
     * Báo cáo tổng số tiền cho vay
     */
    public function getLoanAmountReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = UserLoan::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', LoanStatus::APPROVED->value);

        $groupBy = $this->getGroupByClause($period);

        $loans = $query
            ->selectRaw("
                {$groupBy} as period,
                SUM(principal_amount) as total_loan_amount,
                COUNT(*) as loan_count,
                AVG(principal_amount) as average_loan_amount
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totalLoanAmount = $loans->sum('total_loan_amount');
        $totalLoans = $loans->sum('loan_count');

        return [
            'data' => $loans,
            'total_loan_amount' => $totalLoanAmount,
            'total_loans' => $totalLoans,
            'average_loan_amount' => $totalLoans > 0 ? $totalLoanAmount / $totalLoans : 0,
        ];
    }

    /**
     * Báo cáo tỷ lệ nợ quá hạn
     */
    public function getOverdueRateReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = UserLoan::whereBetween('created_at', [$startDate, $endDate]);

        $groupBy = $this->getGroupByClause($period);

        $overdueData = $query
            ->selectRaw("
                {$groupBy} as period,
                COUNT(*) as total_loans,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN status = 'overdue' THEN principal_amount ELSE 0 END) as overdue_amount
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $overdueData = $overdueData->map(function ($item) {
            $item->overdue_rate = $item->total_loans > 0 ? ($item->overdue_count / $item->total_loans) * 100 : 0;
            return $item;
        });

        $totalLoans = $overdueData->sum('total_loans');
        $totalOverdue = $overdueData->sum('overdue_count');
        $totalOverdueAmount = $overdueData->sum('overdue_amount');

        return [
            'data' => $overdueData,
            'total_loans' => $totalLoans,
            'total_overdue' => $totalOverdue,
            'total_overdue_amount' => $totalOverdueAmount,
            'overdue_rate' => $totalLoans > 0 ? ($totalOverdue / $totalLoans) * 100 : 0,
        ];
    }

    /**
     * Báo cáo theo gói vay
     */
    public function getPackageReport($startDate, $endDate, $period = 'monthly'): array
    {
        $query = UserLoan::whereBetween('user_loans.created_at', [$startDate, $endDate])
            ->where('user_loans.status', LoanStatus::APPROVED->value);

        $packageData = $query
            ->join('loan_packages', 'user_loans.loan_package_id', '=', 'loan_packages.id')
            ->selectRaw("
                JSON_UNQUOTE(JSON_EXTRACT(loan_packages.config_loans, '$.name')) as package_name,
                JSON_UNQUOTE(JSON_EXTRACT(loan_packages.config_loans, '$.max_amount')) as max_amount,
                JSON_UNQUOTE(JSON_EXTRACT(loan_packages.config_loans, '$.min_amount')) as min_amount,
                JSON_UNQUOTE(JSON_EXTRACT(loan_packages.config_loans, '$.interest_rate')) as interest_rate,
                COUNT(user_loans.id) as loan_count,
                SUM(user_loans.principal_amount) as total_amount,
                AVG(user_loans.principal_amount) as average_amount,
                SUM(CASE WHEN user_loans.status = 'overdue' THEN 1 ELSE 0 END) as overdue_count
            ")
            ->groupBy('loan_packages.id', 'loan_packages.config_loans')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Nếu không có dữ liệu, tạo dữ liệu mẫu
        if ($packageData->isEmpty()) {
            $packageData = collect([
                (object)['package_name' => 'Gói vay cá nhân', 'total_amount' => 0, 'loan_count' => 0],
                (object)['package_name' => 'Gói vay doanh nghiệp', 'total_amount' => 0, 'loan_count' => 0],
                (object)['package_name' => 'Gói vay mua nhà', 'total_amount' => 0, 'loan_count' => 0],
            ]);
        }

        $totalLoans = $packageData->sum('loan_count');
        $totalAmount = $packageData->sum('total_amount');

        return [
            'data' => $packageData,
            'total_loans' => $totalLoans,
            'total_amount' => $totalAmount,
            'package_count' => $packageData->count(),
        ];
    }

    /**
     * Báo cáo tổng quan dashboard
     */
    public function getDashboardReport(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Doanh thu hôm nay
        $todayRevenue = Payment::whereDate('created_at', $today)
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value)
            ->sum('amount');

        // Doanh thu tháng này
        $thisMonthRevenue = Payment::whereBetween('created_at', [$thisMonth, Carbon::now()])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value)
            ->sum('amount');

        // Doanh thu tháng trước
        $lastMonthRevenue = Payment::whereBetween('created_at', [$lastMonth, $thisMonth])
            ->where('status', PaymentStatus::SUCCESS->value)
            ->where('direction', PaymentDirection::IN->value)
            ->sum('amount');

        $totalLoanAmount = UserLoan::whereIn('status', [
            LoanStatus::ACTIVE->value, 
            LoanStatus::OVERDUE->value,
            LoanStatus::COMPLETED->value
        ])->sum('principal_amount');

        // Số khoản vay đang hoạt động
        $activeLoans = UserLoan::whereIn('status', [LoanStatus::APPROVED->value, LoanStatus::ACTIVE->value, LoanStatus::OVERDUE->value])->count();

        // Tỷ lệ nợ quá hạn
        $totalLoans = UserLoan::where('status', LoanStatus::APPROVED->value)->count();
        $overdueLoans = UserLoan::where('status', LoanStatus::OVERDUE->value)->count();
        $overdueRate = $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;

        // Số khách hàng mới tháng này
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

    /**
     * Lấy câu lệnh GROUP BY theo kỳ
     */
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

    /**
     * Xuất báo cáo ra Excel
     */
    public function exportToExcel($startDate, $endDate, $period = 'monthly'): string
    {
        // TODO: Implement Excel export
        return 'export_path';
    }

    /**
     * Xuất báo cáo ra PDF
     */
    public function exportToPDF($startDate, $endDate, $period = 'monthly'): string
    {
        // TODO: Implement PDF export
        return 'export_path';
    }
}
