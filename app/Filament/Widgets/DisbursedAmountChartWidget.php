<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class DisbursedAmountChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    protected ReportService $reportService;

    public function boot(ReportService $reportService): void
    {
        $this->reportService = $reportService;
    }

    public function getHeading(): string
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();
        
        $disbursedData = $this->reportService->getDisbursedAmountReport($startDate, $endDate, 'monthly');
        $totalDisbursed = number_format($disbursedData['total_disbursed']);
        
        return "Số tiền đã giải ngân theo tháng ({$totalDisbursed} VND)";
    }

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        $disbursedData = $this->reportService->getDisbursedAmountReport($startDate, $endDate, 'monthly');

        $labels = [];
        $data = [];

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('Y-m');
        }
        
        $labels = [];
        $data = [];
        
        foreach ($months as $month) {
            $labels[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            
            $monthData = $disbursedData['data']->where('period', $month)->first();
            $data[] = $monthData ? $monthData->total_disbursed : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Số tiền giải ngân (VND)',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)', // Blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return new Intl.NumberFormat("vi-VN").format(value) + " VND"; }'
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Giải ngân: " + new Intl.NumberFormat("vi-VN").format(context.parsed.y) + " VND"; }'
                    ],
                ],
            ],
        ];
    }
}
