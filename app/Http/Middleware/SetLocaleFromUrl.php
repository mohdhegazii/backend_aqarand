<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $default = 'ar';
        $supported = ['ar', 'en'];

        $segment = $request->segment(1);

        // Debug logging as requested
        Log::info('Locale debug', [
            'url'            => $request->fullUrl(),
            'segment_1'      => $segment,
            'app_locale_before' => App::getLocale(),
            'config_locale'  => config('app.locale'),
            'session_locale' => session('locale'),
        ]);

        // If the first segment is 'ar', we redirect to remove it (enforce no-prefix for default)
        if ($segment === $default) {
            $segments = $request->segments();
            array_shift($segments); // Remove 'ar'

            $newPath = implode('/', $segments);
            $query = $request->getQueryString();
            $newUrl = $newPath . ($query ? '?'.$query : '');

            // Redirect to root if path is empty, otherwise to the path without 'ar'
            return redirect($newUrl ?: '/');
        }

        // If segment is supported (e.g. 'en') and NOT default
        if (in_array($segment, $supported, true) && $segment !== $default) {
            App::setLocale($segment);
            $currentLocale = $segment;

            // Help generate URLs for localized routes
            URL::defaults(['locale' => $segment]);
        } else {
            // Default case (Arabic) - segment is not a supported locale code (it's a route or empty)
            App::setLocale($default);
            $currentLocale = $default;
        }

        $request->attributes->set('current_locale', $currentLocale);

        // Remove the locale parameter so resource controllers receive the expected arguments
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
