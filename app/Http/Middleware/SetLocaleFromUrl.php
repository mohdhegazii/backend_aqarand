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
        $locale = $request->route('locale');

        if (! in_array($locale, ['en', 'ar'])) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        session(['locale' => $locale]);

        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
