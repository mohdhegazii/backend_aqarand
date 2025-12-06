# Localization and locale routing

## Default locale
- Arabic (`ar`) is the default and fallback locale.
- Arabic URLs never include a locale prefix (home is `/`).
- Other supported locales currently include English (`en`) with a required prefix (e.g., `/en/`).

## Locale detection
- `App\Http\Middleware\SetLocaleFromUrl` inspects the first URL segment on every web request.
- If the first segment matches a supported, non-default locale (e.g., `en`), that locale is applied and stored in the session. Requests to `/en` are normalized to `/en/`.
- If the segment is the default locale (`/ar/...`), the middleware redirects to the same path without the `ar` prefix.
- If the first segment is not a supported locale, the request continues in Arabic without forcing a redirect.

## Route structure
- Arabic routes stay unprefixed under the standard `web` middleware.
- Localized routes live under a `{locale}` prefix that excludes `ar`, using the same controllers and names prefixed with `localized.` (for example, `localized.home`, `localized.admin.dashboard`).
- Admin routes are available for both default and localized URLs.

## Language switcher
- A reusable Blade partial (`resources/views/partials/lang-switcher.blade.php`) shows Arabic and English links.
- Links respect the current path: Arabic links drop any locale prefix, while English links add `/en/`.

## Adding a new language
1. Add the locale code to `supported_locales` in `config/app.php` and set translations in `resources/lang/<locale>/`.
2. Create translation files (for example, `resources/lang/fr/messages.php`).
3. The middleware and route groups will automatically honor the new locale at `/fr/…` without changing route definitions.

## Testing locally
- Visit `http://127.0.0.1:8000/` to see the Arabic home page and confirm `app()->getLocale()` is `ar`.
- Visit `http://127.0.0.1:8000/en/` to see English content and verify `app()->getLocale()` is `en`.
- Check URLs like `/projects` (Arabic) and `/en/projects` (English) to ensure the locale-specific routes render.
