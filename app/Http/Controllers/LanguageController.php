<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function switch(Request $request, string $targetLocale): RedirectResponse
    {
        $supportedLocales = config('app.supported_locales', ['ar', 'en']);
        $defaultLocale = config('app.locale', 'ar');

        if (! in_array($targetLocale, $supportedLocales, true)) {
            abort(400);
        }

        session(['locale' => $targetLocale]);
        App::setLocale($targetLocale);

        $previousPath = trim(parse_url(url()->previous(), PHP_URL_PATH) ?? '/', '/');
        $segments = $previousPath === '' ? [] : explode('/', $previousPath);
        $firstSegment = $segments[0] ?? null;

        // If the first segment is a supported locale (e.g. 'en'), remove it to get the base path
        if ($firstSegment && in_array($firstSegment, $supportedLocales, true) && $firstSegment !== $defaultLocale) {
            array_shift($segments);
        }

        // Also handle the case where default locale might be present in URL (though we try to avoid it)
        if ($firstSegment === $defaultLocale) {
             array_shift($segments);
        }

        $relativePath = implode('/', $segments);

        // Construct target path
        $targetPath = $targetLocale === $defaultLocale
            ? ($relativePath === '' ? '/' : '/'.$relativePath)
            : '/'.$targetLocale.'/'.($relativePath === '' ? '' : $relativePath);

        return redirect()->to($targetPath);
    }
}
