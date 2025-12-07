<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class LocalizationNavigationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load the helper file
        $helperPath = app_path('Helpers/locale_routes.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share current locale with all views
        // This ensures $currentLocale is available in all Blade templates
        View::share('currentLocale', app()->getLocale());
    }
}
