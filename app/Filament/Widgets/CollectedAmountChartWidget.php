<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class CollectedAmountChartWidget extends ChartWidget
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
        
        $collectedData = $this->reportService->getCollectedAmountReport($startDate, $endDate, 'monthly');
        $totalCollected = number_format($collectedData['total_collected']);
        
        return "Số tiền đã thu về theo tháng ({$totalCollected} VND)";
    }

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        $collectedData = $this->reportService->getCollectedAmountReport($startDate, $endDate, 'monthly');

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
            
            $monthData = $collectedData['data']->where('period', $month)->first();
            $data[] = $monthData ? $monthData->total_collected : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Số tiền thu về (VND)',
                    'data' => $data,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
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
                        'label' => 'function(context) { return "Thu về: " + new Intl.NumberFormat("vi-VN").format(context.parsed.y) + " VND"; }'
                    ],
                ],
            ],
        ];
    }
}
