<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\LoanCalculationService;
use App\Services\PaymentService;
use App\Services\UserLoanLogService;
use App\Services\ReportService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->is('admin*')) {
            App::setLocale('vi');
        }
    }
}
