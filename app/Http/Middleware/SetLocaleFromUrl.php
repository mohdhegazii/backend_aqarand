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
        $supportedLocales = config('app.supported_locales', []);
        $defaultLocale = config('app.locale', 'ar');
        $localeFromSegment = $request->segment(1);

        // Redirect requests that try to use the default locale as a prefix back to the clean URL.
        if ($localeFromSegment === $defaultLocale) {
            $cleanedPath = ltrim($request->path(), '/');
            $cleanedPath = ltrim(substr($cleanedPath, strlen($defaultLocale)), '/');
            $targetPath = $cleanedPath === '' ? '/' : '/'.$cleanedPath;

            return redirect()->to($this->appendQueryString($targetPath, $request->getQueryString()));
        }

        // Set the locale from the first URL segment when it is supported and not the default locale.
        if ($localeFromSegment && in_array($localeFromSegment, $supportedLocales, true) && $localeFromSegment !== $defaultLocale) {
            // Normalize URLs like "/en" to have a trailing slash for consistency.
            if ($request->path() === $localeFromSegment) {
                $normalizedPath = '/'.$localeFromSegment.'/';

                return redirect()->to($this->appendQueryString($normalizedPath, $request->getQueryString()));
            }

            App::setLocale($localeFromSegment);
            URL::defaults(['locale' => $localeFromSegment]);
            session(['locale' => $localeFromSegment]);
        } else {
            // Fallback to the default locale without forcing a redirect when the segment is unsupported or missing.
            App::setLocale($defaultLocale);
            session(['locale' => $defaultLocale]);
        }

        return $next($request);
    }

    private function appendQueryString(string $path, ?string $query): string
    {
        if ($query === null || $query === '') {
            return $path;
        }

        return rtrim($path, '?').'?' . $query;
    }
}
