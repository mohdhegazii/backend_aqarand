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

        if ($firstSegment && in_array($firstSegment, $supportedLocales, true)) {
            array_shift($segments);
        }

        $relativePath = implode('/', $segments);

        $targetPath = $targetLocale === $defaultLocale
            ? ($relativePath === '' ? '/' : '/'.$relativePath)
            : '/'.$targetLocale.'/'.($relativePath === '' ? '' : $relativePath);

        if (Auth::check() && Auth::user()->is_admin) {
            if ($relativePath === '') {
                return redirect()->to(route($this->adminRoutePrefix().'dashboard'));
            }

            return redirect()->to($targetPath);
        }

        return redirect()->to($targetPath);
    }
}
