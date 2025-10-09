<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bộ lọc báo cáo')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Từ ngày')
                            ->default(now()->subMonths(11)->startOfMonth()),
                        DatePicker::make('endDate')
                            ->label('Đến ngày')
                            ->default(now()->endOfMonth()),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
