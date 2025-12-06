# Localization Documentation

This project supports multi-language functionality with **Arabic (ar)** as the default locale and **English (en)** (plus potentially others) as secondary locales.

## 1. URL Structure

- **Arabic (Default):** No prefix.
  - Home: `http://domain.com/`
  - Projects: `http://domain.com/projects`

- **English (and other locales):** With prefix.
  - Home: `http://domain.com/en/`
  - Projects: `http://domain.com/en/projects`

## 2. Locale Detection & Middleware

The system uses a custom middleware `App\Http\Middleware\SetLocaleFromUrl` to determine the locale:

1.  **Check First URL Segment:** The middleware examines the first segment of the URL path (e.g., `en`, `fr`).
2.  **Supported Locale:** If the segment matches a supported locale (defined in `config/app.supported_locales`) and is **not** the default locale (`ar`), the application locale is set to that language.
    - Example: `/en/projects` sets locale to `en`.
3.  **Default Locale:** If the segment is not a supported locale (or is empty), the application assumes the default locale (`ar`).
    - Example: `/projects` (no prefix) -> locale is `ar`.
4.  **Redirects:**
    - If a user tries to access `/ar/projects`, they are redirected to `/projects` (canonical URL).
    - If a user accesses `/en` (without trailing slash), they are redirected to `/en/` for consistency.

## 3. Routes Configuration (`routes/web.php`)

Routes are organized into two main groups:

1.  **Default Group (Arabic):**
    - Middleware: `['web', 'set.locale']`
    - No prefix.
    - Contains all routes for the default language.

2.  **Localized Group:**
    - Prefix: `{locale}`
    - Middleware: `['web', 'set.locale']`
    - Contains all routes for other languages.
    - The `{locale}` parameter is constrained to match supported locales *excluding* the default one (regex: `^(?!ar$)[a-zA-Z_]{2,5}$`).

## 4. Adding a New Language

To add a new language (e.g., French `fr`):

1.  **Update Config:** Add `'fr'` to `supported_locales` in `config/app.php` (if you added this custom config key) or ensure the middleware logic recognizes it.
2.  **Add Translations:** Create a new directory `resources/lang/fr/` and add necessary files (e.g., `messages.php`).
3.  **Update Switcher:** The language switcher automatically detects supported locales if configured dynamically, or you can add the link manually in `resources/views/partials/lang-switcher.blade.php`.

## 5. Language Switcher

A blade partial is available at `resources/views/partials/lang-switcher.blade.php`. It generates links to the current page in the alternate language, preserving the remaining path segments.

## 6. Testing

- Access `/` -> Should show Arabic content.
- Access `/en/` -> Should show English content.
- Access `/en/admin` -> Should show English Admin Dashboard.
- Access `/admin` -> Should show Arabic Admin Dashboard.
