<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        /*
         * Every paginated table renders through the shared number-only view,
         * so no default "previous / next" controls can ever leak into the UI.
         */
        Paginator::defaultView('pagination.numbers');
    }
}
