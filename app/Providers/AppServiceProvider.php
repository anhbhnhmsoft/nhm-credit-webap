<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\LoanCalculationService;
use App\Services\PageStaticService;
use App\Services\PaymentService;
use App\Services\UserLoanLogService;
use App\Services\ReportService;
use App\Services\BankService;
use App\Services\BankAccountService;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use App\Services\UserLoanService;
use App\Models\UserLoan;
use App\Models\UserLoanLog;
use App\Observers\UserLoanObserver;
use App\Observers\UserLoanLogObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, fn() => new AuthService());
        $this->app->singleton(LoanCalculationService::class, fn() => new LoanCalculationService());
        $this->app->singleton(PaymentService::class, fn() => new PaymentService());
        $this->app->singleton(UserLoanLogService::class, fn() => new UserLoanLogService());
        $this->app->singleton(ReportService::class, fn() => new ReportService());
        $this->app->singleton(BankService::class, fn() => new BankService());
        $this->app->singleton(BankAccountService::class, fn($app) => new BankAccountService($app->make(\App\Services\AuthService::class)));
        $this->app->singleton(PageStaticService::class, fn() => new PageStaticService());
        $this->app->singleton(UserLoanService::class, fn() => new UserLoanService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->is('admin*')) {
            App::setLocale('vi');
        }
        
        UserLoan::observe(UserLoanObserver::class);
        UserLoanLog::observe(UserLoanLogObserver::class);
    }
}
