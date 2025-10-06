<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserLoanLogService;

class CheckTimeUserLoanLog extends Command
{
    protected $signature = 'app:check-time-user-loan-log';
    protected $description = 'Check time user loan log';

    public function handle(UserLoanLogService $userLoanLogService)
    {
        $result = $userLoanLogService->generateDailyLogs();
        $this->info($result['message'] ?? 'Done');
        return Command::SUCCESS;
    }
}