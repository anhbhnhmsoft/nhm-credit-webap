<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportStatsWidget extends BaseWidget
{
    protected ReportService $reportService;

    public function boot(ReportService $reportService): void
    {
        $this->reportService = $reportService;
    }

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $dashboardData = $this->reportService->getDashboardReport();
        return [
            Stat::make('Doanh thu hôm nay', number_format($dashboardData['today_revenue']) . ' VND')
                ->description('Tăng ' . number_format($dashboardData['revenue_growth'], 1) . '% so với tháng trước')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Tổng tiền cho vay', number_format($dashboardData['total_loan_amount']) . ' VND')
                ->description($dashboardData['active_loans'] . ' khoản vay đang hoạt động')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Tỷ lệ nợ quá hạn', number_format($dashboardData['overdue_rate'], 2) . '%')
                ->description($dashboardData['new_customers'] . ' khách hàng mới tháng này')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($dashboardData['overdue_rate'] > 10 ? 'danger' : 'warning'),

            Stat::make('Khách hàng mới', $dashboardData['new_customers'])
                ->description('Tháng ' . Carbon::now()->format('m/Y'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
        ];
    }
}
