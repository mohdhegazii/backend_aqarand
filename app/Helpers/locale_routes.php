<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

if (! function_exists('localized_route')) {
    /**
     * Generate a route URL that respects the current locale.
     *
     * - For Arabic (default locale): NO locale prefix in URL.
     * - For English (or other non-default locales): use locale in URL if the route group expects it.
     *
     * @param string $name The base route name (e.g., 'admin.projects.index')
     * @param array $parameters Route parameters
     * @param bool $absolute Whether to generate an absolute URL
     * @return string
     */
    function localized_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $locale = App::getLocale();

        // Default locale (Arabic) - Use the route as is (assuming it's the base name)
        if ($locale === 'ar') {
            return route($name, $parameters, $absolute);
        }

        // Non-default locale (English)
        // We need to find the correct localized route name.
        // Based on routes/web.php, English routes are prefixed.
        // We try common patterns to ensure robustness.

        $candidates = [
            'localized.' . $name,           // Most likely: localized.admin.dashboard
            'localized.localized.' . $name, // Possible double prefixing from nested groups
        ];

        // Ensure locale is in parameters
        $parameters = array_merge(['locale' => $locale], $parameters);

        foreach ($candidates as $candidate) {
            if (Route::has($candidate)) {
                return route($candidate, $parameters, $absolute);
            }
        }

        // Fallback: Use the original name with the locale parameter.
        // This handles cases where the route might not have a name prefix but expects the parameter,
        // or effectively falls back to a query string parameter if the route doesn't accept it.
        return route($name, $parameters, $absolute);
    }
}
