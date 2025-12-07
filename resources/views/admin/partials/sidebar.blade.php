@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';

    $matchesRoute = function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern) || request()->routeIs('localized.' . $pattern)) {
                return true;
            }
        }

        return false;
    };

    $sections = [
        'files' => [
            'label' => $isRtl ? 'إدارة الملفات' : 'File Manager',
            'items' => [
                [
                    'label' => __('admin.file_manager'),
                    'route' => 'admin.file_manager',
                    'icon' => 'bi-folder2-open',
                    'active' => $matchesRoute(['admin.file_manager']),
                ],
            ],
        ],
        'real-estate' => [
            'label' => $isRtl ? 'العقارات' : 'Real Estate',
            'items' => [
                [
                    'label' => $isRtl ? 'المطوّرون' : 'Developers',
                    'route' => 'admin.developers.index',
                    'icon' => 'bi-people',
                    'active' => $matchesRoute(['admin.developers.*']),
                ],
                [
                    'label' => $isRtl ? 'المشاريع' : 'Projects',
                    'route' => 'admin.projects.index',
                    'icon' => 'bi-building',
                    'active' => $matchesRoute(['admin.projects.*']),
                ],
                [
                    'label' => $isRtl ? 'الوحدات' : 'Units',
                    'route' => 'admin.units.index',
                    'icon' => 'bi-house-door',
                    'active' => $matchesRoute(['admin.units.*']),
                ],
                [
                    'label' => $isRtl ? 'القوائم' : 'Listings',
                    'route' => 'admin.listings.index',
                    'icon' => 'bi-card-checklist',
                    'active' => $matchesRoute(['admin.listings.*']),
                ],
            ],
        ],
        'settings' => [
            'label' => $isRtl ? 'الإعدادات' : 'Settings',
            'items' => [
                ['heading' => $isRtl ? 'المواقع' : 'Locations'],
                [
                    'label' => $isRtl ? 'الدول' : 'Countries',
                    'route' => 'admin.countries.index',
                    'icon' => 'bi-geo-alt',
                    'active' => $matchesRoute(['admin.countries.*']),
                ],
                [
                    'label' => $isRtl ? 'المناطق' : 'Regions',
                    'route' => 'admin.regions.index',
                    'icon' => 'bi-map',
                    'active' => $matchesRoute(['admin.regions.*']),
                ],
                [
                    'label' => $isRtl ? 'المدن' : 'Cities',
                    'route' => 'admin.cities.index',
                    'icon' => 'bi-buildings',
                    'active' => $matchesRoute(['admin.cities.*']),
                ],
                [
                    'label' => $isRtl ? 'الأحياء' : 'Districts',
                    'route' => 'admin.districts.index',
                    'icon' => 'bi-diagram-3',
                    'active' => $matchesRoute(['admin.districts.*']),
                ],
                ['heading' => $isRtl ? 'التصنيفات وأنواع العقارات' : 'Classifications'],
                [
                    'label' => $isRtl ? 'الفئات' : 'Categories',
                    'route' => 'admin.categories.index',
                    'icon' => 'bi-tags',
                    'active' => $matchesRoute(['admin.categories.*']),
                ],
                [
                    'label' => $isRtl ? 'أنواع العقارات' : 'Property Types',
                    'route' => 'admin.property-types.index',
                    'icon' => 'bi-houses',
                    'active' => $matchesRoute(['admin.property-types.*']),
                ],
                [
                    'label' => $isRtl ? 'أنواع الوحدات' : 'Unit Types',
                    'route' => 'admin.unit-types.index',
                    'icon' => 'bi-grid',
                    'active' => $matchesRoute(['admin.unit-types.*']),
                ],
                ['heading' => $isRtl ? 'المرافق والخدمات' : 'Amenities'],
                [
                    'label' => $isRtl ? 'تصنيفات المرافق والخدمات' : 'Amenity Categories',
                    'route' => 'admin.amenity-categories.index',
                    'icon' => 'bi-collection',
                    'active' => $matchesRoute(['admin.amenity-categories.*']),
                ],
                [
                    'label' => $isRtl ? 'المرافق' : 'Amenities',
                    'route' => 'admin.amenities.index',
                    'icon' => 'bi-lightning',
                    'active' => $matchesRoute(['admin.amenities.*']),
                ],
            ],
        ],
    ];

    $defaultOpenSection = null;

    foreach ($sections as $key => $sectionData) {
        foreach ($sectionData['items'] as $item) {
            if (! empty($item['active'])) {
                $defaultOpenSection = $key;
                break 2;
            }
        }
    }
@endphp

<nav x-data="sidebarAccordion('{{ $defaultOpenSection ?? '' }}')" class="mt-4 px-2 space-y-3 overflow-y-auto max-h-[calc(100vh-4rem)]">
    <div class="px-4 mb-2">
        <a href="{{ localized_route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') || request()->routeIs('localized.admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : '' }}">
            <i class="bi bi-speedometer2 text-lg"></i>
            <span>{{ __('admin.dashboard') }}</span>
        </a>
    </div>

    @foreach ($sections as $sectionKey => $section)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <button type="button" @click="toggle('{{ $sectionKey }}')" class="w-full flex items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50" :aria-expanded="isOpen('{{ $sectionKey }}')">
                <span>{{ $section['label'] }}</span>
                <i class="bi bi-chevron-down transition-transform duration-200" :class="isOpen('{{ $sectionKey }}') ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="isOpen('{{ $sectionKey }}')" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="border-t border-gray-100 bg-gray-50">
                <ul class="py-2 space-y-1">
                    @foreach ($section['items'] as $item)
                        @if(isset($item['heading']))
                            <li class="px-4 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $item['heading'] }}</li>
                            @continue
                        @endif
                        @php
                            $isActive = $item['active'] ?? false;
                            $activeClasses = $isActive ? 'bg-indigo-50 text-indigo-700 font-semibold ' . ($isRtl ? 'border-r-4' : 'border-l-4') . ' border-indigo-500' : 'text-gray-700 hover:bg-white';
                        @endphp
                        <li>
                            <a href="{{ localized_route($item['route']) }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg transition {{ $activeClasses }}">
                                <i class="bi {{ $item['icon'] }} text-base"></i>
                                <span class="flex-1">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</nav>

@push('scripts')
    <script>
        function sidebarAccordion(defaultSection) {
            return {
                openSection: defaultSection || null,
                toggle(section) {
                    this.openSection = this.openSection === section ? null : section;
                },
                isOpen(section) {
                    return this.openSection === section;
                }
            }
        }
    </script>
@endpush
