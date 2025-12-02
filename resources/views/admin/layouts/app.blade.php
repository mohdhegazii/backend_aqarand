<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@lang('admin.app_name')</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Minimal RTL support */
        [dir="rtl"] .space-x-4 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
            margin-right: calc(1rem * var(--tw-space-x-reverse));
            margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
        }
        [dir="rtl"] {
            text-align: right;
        }
        [dir="rtl"] .border-r {
            border-left-width: 1px;
            border-right-width: 0;
        }
        [dir="rtl"] .ml-auto {
            margin-right: auto;
            margin-left: 0;
        }
    </style>
    <!-- CDN for Tailwind (optional fallback if build fails) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0">
            <div class="p-4 border-b border-gray-200">
                <span class="text-xl font-bold">@lang('admin.app_name')</span>
            </div>
            <nav class="mt-4 px-2 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                    @lang('admin.dashboard')
                </a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.countries') / @lang('admin.regions')
                </div>
                <a href="{{ route('admin.countries.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.countries')</a>
                <a href="{{ route('admin.regions.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.regions')</a>
                <a href="{{ route('admin.cities.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.cities')</a>
                <a href="{{ route('admin.districts.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.districts')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.property_types') / @lang('admin.unit_types')
                </div>
                <a href="{{ route('admin.property-types.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.property_types')</a>
                <a href="{{ route('admin.unit-types.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.unit_types')</a>
                <a href="{{ route('admin.amenities.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.amenities')</a>
                <a href="{{ route('admin.developers.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.developers')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    Taxonomies
                </div>
                <a href="{{ route('admin.segments.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.segments')</a>
                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.categories')</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        @yield('header', __('admin.dashboard'))
                    </h2>
                    <div class="flex items-center space-x-4">
                        <!-- Language Switcher -->
                        @if(app()->getLocale() === 'en')
                            <a href="{{ route('lang.switch', 'ar') }}" class="text-gray-600 hover:text-gray-900 font-bold">العربية</a>
                        @else
                            <a href="{{ route('lang.switch', 'en') }}" class="text-gray-600 hover:text-gray-900 font-bold">English</a>
                        @endif

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 underline">
                                @lang('admin.logout')
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
