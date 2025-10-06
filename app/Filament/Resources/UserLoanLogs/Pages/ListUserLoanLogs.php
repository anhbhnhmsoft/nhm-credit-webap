<?php

namespace App\Filament\Resources\UserLoanLogs\Pages;

use App\Filament\Resources\UserLoanLogs\UserLoanLogsResource;
use App\Utils\Constants\LoanLogStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUserLoanLogs extends ListRecords
{
    protected static string $resource = UserLoanLogsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'Tất cả' => Tab::make(),
            'Chờ thanh toán' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', LoanLogStatus::PENDING->value)),
            'Thanh toán một phần' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', LoanLogStatus::PARTIAL->value)),
            'Đã thanh toán' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', LoanLogStatus::PAID->value)),
            'Quá hạn' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', LoanLogStatus::OVERDUE->value)),

        ];
    }
}
