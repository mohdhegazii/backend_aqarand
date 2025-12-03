<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

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

        if ($previousUrl && $previousUrl !== url()->current()) {
            return redirect()->to($previousUrl);
        }

        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/');
    }
}
