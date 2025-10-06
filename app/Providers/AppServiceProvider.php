<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\LoanCalculationService;
use App\Services\UserLoanLogService;
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
        $this->app->singleton(UserLoanLogService::class, fn() => new UserLoanLogService());
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
