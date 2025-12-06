@php
    $supportedLocales = config('app.supported_locales', []);
    $defaultLocale = config('app.locale', 'ar');
    $currentLocale = app()->getLocale();
    $currentPath = trim(parse_url(url()->current(), PHP_URL_PATH) ?? '/', '/');
    $segments = $currentPath === '' ? [] : explode('/', $currentPath);
    $firstSegment = $segments[0] ?? null;

    if ($firstSegment && in_array($firstSegment, $supportedLocales, true) && $firstSegment !== $defaultLocale) {
        array_shift($segments);
    }

    $relativePath = implode('/', $segments);
    $arabicUrl = url($relativePath === '' ? '/' : '/'.$relativePath);
    $englishUrl = url('/en/'.($relativePath === '' ? '' : $relativePath));
@endphp
<div class="flex items-center space-x-2 rtl:space-x-reverse">
    <span class="text-sm font-semibold">@lang('messages.language'):</span>
    <a href="{{ $arabicUrl }}" class="text-blue-700 hover:underline {{ $currentLocale === 'ar' ? 'font-bold' : '' }}">@lang('messages.switch_to_ar')</a>
    <span class="text-gray-400">|</span>
    <a href="{{ $englishUrl }}" class="text-blue-700 hover:underline {{ $currentLocale === 'en' ? 'font-bold' : '' }}">@lang('messages.switch_to_en')</a>
</div>
