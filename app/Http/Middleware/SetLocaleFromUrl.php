<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        // Check the first segment of the URL
        $segment = $request->segment(1);

        if ($segment === 'en') {
            $locale = 'en';
        } else {
            $locale = 'ar';
        }

        App::setLocale($locale);
        // We do NOT set URL::defaults here blindly because 'ar' routes don't have the prefix.
        // We only persist 'locale' if we are in a route group that expects it?
        // Actually, for 'ar', we don't want the prefix.

        session(['locale' => $locale]);

        // If the route has a 'locale' parameter (captured by {locale?}), forget it so controllers don't get it.
        if ($request->route('locale')) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
