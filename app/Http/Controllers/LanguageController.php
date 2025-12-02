<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supportedLocales = config('app.supported_locales', ['en', 'ar']);

        if (! in_array($locale, $supportedLocales, true)) {
            abort(400);
        }

        session(['locale' => $locale]);
        App::setLocale($locale);

        $previousUrl = url()->previous();

        if ($previousUrl) {
            $parsed = parse_url($previousUrl);
            $path = ltrim($parsed['path'] ?? '', '/');
            $segments = $path === '' ? [] : explode('/', $path);

            if (in_array($segments[0] ?? '', $supportedLocales, true)) {
                $segments[0] = $locale;
            } elseif (($segments[0] ?? '') === 'admin') {
                array_unshift($segments, $locale);
            }

            $localizedPath = '/' . ltrim(implode('/', $segments), '/');
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';

            return redirect(URL::to($localizedPath . $query));
        }

        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard', ['locale' => $locale]);
        }

        return redirect('/');
    }
}
