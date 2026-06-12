<?php

namespace App\Providers;

use App\Policies\EmployeeWorkReportPolicy;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewAnyEmployeeWorkReports', [EmployeeWorkReportPolicy::class, 'viewAny']);
        Gate::define('viewEmployeeWorkReport', [EmployeeWorkReportPolicy::class, 'view']);

        Vite::prefetch(concurrency: 3);

        Broadcast::routes(['middleware' => ['web', 'auth', 'verified']]);
    }
}
