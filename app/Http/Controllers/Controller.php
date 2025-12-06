<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function adminRoutePrefix(): string
    {
        $locale = app()->getLocale();

        return $locale === config('app.locale') ? 'admin.' : 'localized.admin.';
    }

    protected function adminRoute(string $name, array $parameters = [], bool $absolute = true): string
    {
        return route($this->adminRoutePrefix().$name, $parameters, $absolute);
    }
}
