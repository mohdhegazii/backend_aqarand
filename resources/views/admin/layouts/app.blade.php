<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@lang('admin.app_name')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    @stack('styles')
</head>
<body class="aqarand-body">
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
        $isActive = fn($route) => request()->routeIs($route);
        $navSections = [
            [
                'links' => [
                    ['route' => $adminRoutePrefix . 'dashboard', 'label' => __('admin.dashboard'), 'icon' => 'bi-grid-1x2'],
                ],
            ],
            [
                'label' => __('admin.countries') . ' / ' . __('admin.regions'),
                'links' => [
                    ['route' => $adminRoutePrefix . 'countries.index', 'label' => __('admin.countries')],
                    ['route' => $adminRoutePrefix . 'regions.index', 'label' => __('admin.regions')],
                    ['route' => $adminRoutePrefix . 'cities.index', 'label' => __('admin.cities')],
                    ['route' => $adminRoutePrefix . 'districts.index', 'label' => __('admin.districts')],
                ],
            ],
            [
                'label' => __('admin.property_types') . ' / ' . __('admin.unit_types'),
                'links' => [
                    ['route' => $adminRoutePrefix . 'property-types.index', 'label' => __('admin.property_types')],
                    ['route' => $adminRoutePrefix . 'unit-types.index', 'label' => __('admin.unit_types')],
                    ['route' => $adminRoutePrefix . 'amenities.index', 'label' => __('admin.amenities')],
                    ['route' => $adminRoutePrefix . 'developers.index', 'label' => __('admin.developers')],
                ],
            ],
            [
                'label' => __('admin.taxonomies'),
                'links' => [
                    ['route' => $adminRoutePrefix . 'categories.index', 'label' => __('admin.categories')],
                ],
            ],
            [
                'label' => __('admin.amenity_categories'),
                'links' => [
                    ['route' => $adminRoutePrefix . 'amenity-categories.index', 'label' => __('admin.amenity_categories')],
                ],
            ],
            [
                'label' => __('admin.real_estate'),
                'links' => [
                    ['route' => $adminRoutePrefix . 'projects.index', 'label' => __('admin.projects')],
                    ['route' => $adminRoutePrefix . 'units.index', 'label' => __('admin.units')],
                    ['route' => $adminRoutePrefix . 'listings.index', 'label' => __('admin.listings')],
                ],
            ],
            [
                'label' => __('admin.media_manager'),
                'links' => [
                    ['route' => $adminRoutePrefix . 'media.index', 'label' => __('admin.file_manager')],
                ],
            ],
        ];
    @endphp

    <div class="app-shell">
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="aqarand-sidebar" data-hidden-class="{{ $isRtl ? 'rtl-hidden' : 'ltr-hidden' }}">
            <div class="brand">
                <div class="logo-mark">AQ</div>
                <div class="brand-text">
                    <div class="card-title">@lang('admin.app_name')</div>
                    <div class="card-subtitle">Dashboard</div>
                </div>
                <button type="button" id="sidebar-close-button" class="ui-btn ui-btn-ghost" aria-label="@lang('admin.close_sidebar')" onclick="toggleSidebar(false)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                @foreach($navSections as $section)
                    <div class="sidebar-group">
                        @if(!empty($section['label']))
                            <div class="group-label">{{ $section['label'] }}</div>
                        @endif
                        @foreach($section['links'] as $link)
                            @php $active = $isActive($link['route']); @endphp
                            <a href="{{ route($link['route']) }}" class="sidebar-link {{ $active ? 'active' : '' }}">
                                @if(isset($link['icon']))
                                    <i class="bi {{ $link['icon'] }}"></i>
                                @else
                                    <span class="icon-dot"></span>
                                @endif
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </aside>

        <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="toggleSidebar(false)"></div>

        <!-- Main Content -->
        <div class="app-surface">
            <!-- Navbar -->
            <header class="layout-header">
                <div class="header-inner">
                    <div class="flex items-center gap-3">
                        <button type="button" id="sidebar-toggle" class="ui-btn ui-btn-ghost" aria-label="@lang('admin.toggle_sidebar')" onclick="toggleSidebar()">
                            <i class="bi bi-list"></i>
                        </button>
                        <div>
                            <div class="title">@yield('header', __('admin.dashboard'))</div>
                            <div class="subtitle">@lang('admin.app_name')</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="badge is-primary">
                             @if(app()->getLocale() === 'ar')
                                <a href="{{ url('en/' . (request()->path() === '/' ? '' : request()->path())) }}" class="hover:underline">English</a>
                             @else
                                @php
                                    $p = request()->path();
                                    if(\Illuminate\Support\Str::startsWith($p, 'en/')) $p = substr($p, 3);
                                    elseif($p === 'en') $p = '';
                                @endphp
                                <a href="{{ url($p) }}" class="hover:underline">العربية</a>
                             @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <x-button variant="outline" type="submit">@lang('admin.logout')</x-button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                        @if(session('success'))
                            <div class="dashboard-card" role="alert">
                                <div class="card-meta">
                                    <span class="badge is-success">{{ __('Success') }}</span>
                                    <span class="card-subtitle">{{ now()->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="card-title">{{ session('success') }}</div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="dashboard-card" role="alert">
                                <div class="card-meta">
                                    <span class="badge is-error">{{ __('Error') }}</span>
                                    <span class="card-subtitle">{{ __('Please review the validation messages') }}</span>
                                </div>
                        <ul class="list-disc ms-5" style="margin-block: var(--spacing-2); color: var(--neutral-4);">
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
        function toggleSidebar(force) {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;

            const isOpen = sidebar.classList.contains('is-open');
            const next = typeof force === 'boolean' ? force : !isOpen;
            sidebar.classList.toggle('is-open', next);
            const shouldShowBackdrop = next && window.innerWidth < 1024;
            backdrop.classList.toggle('is-visible', shouldShowBackdrop);
        }

        window.addEventListener('resize', () => {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;
            if (window.innerWidth >= 1024) {
                sidebar.classList.add('is-open');
                backdrop.classList.remove('is-visible');
            } else {
                sidebar.classList.remove('is-open');
            }
        });

        window.addEventListener('click', function(e) {
            const toggleButton = document.getElementById('sidebar-toggle');
            const closeButton = document.getElementById('sidebar-close-button');
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            const clickedToggle = toggleButton && toggleButton.contains(e.target);
            const clickedClose = closeButton && closeButton.contains(e.target);

            if (backdrop && backdrop.classList.contains('is-visible') && sidebar && !sidebar.contains(e.target) && !clickedToggle && !clickedClose) {
                toggleSidebar(false);
            }
        });

        // keep sidebar open on desktop load
        if (window.innerWidth >= 1024) {
            toggleSidebar(true);
        }
    </script>
</body>
</html>
