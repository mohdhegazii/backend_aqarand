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

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    @if (config('app.debug'))
        <div style="background:#111;color:#0f0;padding:8px;font-size:11px;direction:ltr;z-index:9999;position:relative;">
            <strong>Locale DEBUG</strong><br>
            app()->getLocale(): {{ app()->getLocale() }}<br>
            config('app.locale'): {{ config('app.locale') }}<br>
            URL: {{ url()->current() }}<br>
            Segment[1]: {{ request()->segment(1) ?? 'null' }}<br>
            Session locale: {{ session('locale') ?? 'null' }}
        </div>
    @endif
    @php
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';
        $adminRoutePrefix = $adminRoutePrefix ?? 'admin.';
    @endphp
    <div class="min-h-screen bg-gray-100 flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside id="admin-sidebar" data-hidden-class="{{ $isRtl ? 'translate-x-full' : '-translate-x-full' }}" class="fixed inset-y-0 {{ $isRtl ? 'right-0 translate-x-full' : 'left-0 -translate-x-full' }} w-64 bg-white border-{{ $isRtl ? 'l' : 'r' }} border-gray-200 transform md:translate-x-0 transition-transform duration-200 z-30 md:static md:block">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <span class="text-xl font-bold">@lang('admin.app_name')</span>
                <button type="button" id="sidebar-close-button" class="md:hidden text-gray-500 hover:text-gray-700" aria-label="@lang('admin.close_sidebar')" onclick="toggleSidebar()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <nav class="mt-4 px-2 space-y-1 overflow-y-auto max-h-[calc(100vh-4rem)]">
                <a href="{{ route($adminRoutePrefix.'dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                    @lang('admin.dashboard')
                </a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.countries') / @lang('admin.regions')
                </div>
                <a href="{{ route($adminRoutePrefix.'countries.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.countries')</a>
                <a href="{{ route($adminRoutePrefix.'regions.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.regions')</a>
                <a href="{{ route($adminRoutePrefix.'cities.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.cities')</a>
                <a href="{{ route($adminRoutePrefix.'districts.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.districts')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.property_types') / @lang('admin.unit_types')
                </div>
                <a href="{{ route($adminRoutePrefix.'property-types.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.property_types')</a>
                <a href="{{ route($adminRoutePrefix.'unit-types.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.unit_types')</a>
                <a href="{{ route($adminRoutePrefix.'amenities.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.amenities')</a>
                <a href="{{ route($adminRoutePrefix.'developers.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.developers')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.taxonomies')
                </div>
                <a href="{{ route($adminRoutePrefix.'categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.categories')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.amenity_categories')
                </div>
                <a href="{{ route($adminRoutePrefix.'amenity-categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.amenity_categories')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.real_estate')
                </div>
                <a href="{{ route($adminRoutePrefix.'projects.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.projects')</a>
                <!-- Property Models now accessed via Projects -->
                <a href="{{ route($adminRoutePrefix.'units.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.units')</a>
                <a href="{{ route($adminRoutePrefix.'listings.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.listings')</a>

                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase">
                    @lang('admin.media_manager')
                </div>
                <a href="{{ route($adminRoutePrefix.'media.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">@lang('admin.file_manager')</a>
            </nav>
        </aside>

        <div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 hidden md:hidden z-20" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden min-h-screen">
            <!-- Navbar -->
            <header class="bg-white shadow sticky top-0 z-10">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <button type="button" id="sidebar-toggle" class="md:hidden text-gray-600 hover:text-gray-900" aria-label="@lang('admin.toggle_sidebar')" onclick="toggleSidebar()">
                            <i class="bi bi-list text-2xl"></i>
                        </button>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            @yield('header', __('admin.dashboard'))
                        </h2>
                    </div>
                    <div class="flex items-center space-x-4 rtl:space-x-reverse">
                        <div class="px-2">
                             @if(app()->getLocale() === 'ar')
                                <a href="{{ url('en/' . (request()->path() === '/' ? '' : request()->path())) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">English</a>
                             @else
                                @php
                                    $p = request()->path();
                                    if(\Illuminate\Support\Str::startsWith($p, 'en/')) $p = substr($p, 3);
                                    elseif($p === 'en') $p = '';
                                @endphp
                                <a href="{{ url($p) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">العربية</a>
                             @endif
                        </div>

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

    @stack('scripts')
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;

            const hiddenClass = sidebar.dataset.hiddenClass || '-translate-x-full';
            const willShow = sidebar.classList.contains(hiddenClass);

            sidebar.classList.toggle(hiddenClass);
            backdrop.classList.toggle('hidden', !willShow);
        }

        window.addEventListener('click', function(e) {
            const toggleButton = document.getElementById('sidebar-toggle');
            const closeButton = document.getElementById('sidebar-close-button');
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            const clickedToggle = toggleButton && toggleButton.contains(e.target);
            const clickedClose = closeButton && closeButton.contains(e.target);

            if (backdrop && !backdrop.classList.contains('hidden') && sidebar && !sidebar.contains(e.target) && !clickedToggle && !clickedClose) {
                toggleSidebar();
            }
        });
    </script>
</body>
</html>
