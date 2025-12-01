<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (! in_array($locale, config('app.supported_locales', ['en', 'ar']))) {
            abort(400);
        }

        session(['locale' => $locale]);
        App::setLocale($locale);

        return redirect()->back();
    }
}
