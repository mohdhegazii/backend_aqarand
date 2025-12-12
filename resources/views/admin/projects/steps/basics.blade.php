@extends('admin.layouts.app')

@section('header')
    {{ $project->exists ? __('admin.edit_project') . ': ' . ($project->name_ar ?? $project->name_en) : __('admin.create_new') }}
@endsection

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <!-- Wizard Progress -->
    @include('admin.projects.partials.wizard_steps', ['currentStep' => 1, 'projectId' => $project->id])

    <!-- Step Indicator -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">{{ __('admin.project_wizard.step_basics') }}</h2>
        <p class="text-sm text-gray-500">{{ __('admin.project_wizard.step_1_of_x') }}</p>
    </div>

    <form action="{{ route('admin.projects.steps.basics.store', $project->id) }}" method="POST" id="basics-form">
        @csrf

        <div class="space-y-6">
            <!-- Project Name (Arabic) -->
            <div>
                <label for="name_ar" class="block text-sm font-semibold text-gray-700">
                    {{ __('admin.project_wizard.name_ar') }} <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name_ar"
                    name="name_ar"
                    value="{{ old('name_ar', $project->name_ar ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="{{ __('admin.project_wizard.name_ar_placeholder') }}"
                    required
                >
                @error('name_ar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Project Name (English) -->
            <div>
                <label for="name_en" class="block text-sm font-semibold text-gray-700">
                    {{ __('admin.project_wizard.name_en') }} <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name_en"
                    name="name_en"
                    value="{{ old('name_en', $project->name_en ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="{{ __('admin.project_wizard.name_en_placeholder') }}"
                    required
                >
                @error('name_en')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Launch Date Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Launch Date -->
                <div>
                    <label for="launch_date" class="block text-sm font-semibold text-gray-700">
                        {{ __('admin.project_wizard.launch_date_label') }}
                    </label>
                    <input
                        type="date"
                        id="launch_date"
                        name="launch_date"
                        value="{{ old('launch_date', isset($project->launch_date) ? $project->launch_date->format('Y-m-d') : '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="{{ __('admin.project_wizard.launch_date_placeholder') }}"
                    >
                    @error('launch_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Empty Column for Layout -->
                <div class="hidden md:block">
                    <!-- Placeholder to preserve 2-column layout -->
                </div>
            </div>

            <!-- Developer -->
            <div>
                <label for="developer_id" class="block text-sm font-semibold text-gray-700">
                    {{ __('admin.project_wizard.developer') }} <span class="text-red-500">*</span>
                </label>
                <x-developers.select
                    name="developer_id"
                    :selected-id="old('developer_id', $project->developer_id)"
                />
                @error('developer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- PROJECT LOCATION SECTION -->
            <div class="pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.location') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Country (Hidden by default, used for logic) -->
                    <div class="hidden">
                        <label for="country_id" class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.country') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="country_id"
                            name="country_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                            {{-- We don't need 'Select Option' if we are hiding and defaulting to Egypt --}}
                            @foreach($countries as $country)
                                <option
                                    value="{{ $country->id }}"
                                    data-lat="{{ number_format($country->lat ?? 26.8206, 7, '.', '') }}"
                                    data-lng="{{ number_format($country->lng ?? 30.8025, 7, '.', '') }}"
                                    {{ (old('country_id', $project->country_id) == $country->id || $country->code === 'EG' || $country->name_en === 'Egypt') ? 'selected' : '' }}
                                >
                                    {{ $country->name_local ?? $country->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Region / Governorate -->
                    <div>
                        <label for="region_id" class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.region') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="region_id"
                            name="region_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                            <option value="">{{ __('admin.select_option') }}</option>
                            @foreach($regions as $region)
                                <option
                                    value="{{ $region->id }}"
                                    data-lat="{{ isset($region->lat) ? number_format($region->lat, 7, '.', '') : '' }}"
                                    data-lng="{{ isset($region->lng) ? number_format($region->lng, 7, '.', '') : '' }}"
                                    {{ (old('region_id', $project->region_id) == $region->id) ? 'selected' : '' }}
                                >
                                    {{ $region->name_local ?? $region->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('region_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city_id" class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.city') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="city_id"
                            name="city_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                            {{ $cities->isEmpty() ? 'disabled' : '' }}
                        >
                            <option value="">{{ __('admin.select_option') }}</option>
                            @foreach($cities as $city)
                                <option
                                    value="{{ $city->id }}"
                                    data-lat="{{ isset($city->lat) ? number_format($city->lat, 7, '.', '') : '' }}"
                                    data-lng="{{ isset($city->lng) ? number_format($city->lng, 7, '.', '') : '' }}"
                                    {{ (old('city_id', $project->city_id) == $city->id) ? 'selected' : '' }}
                                >
                                    {{ $city->name_local ?? $city->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- District (Optional) -->
                    <div>
                        <label for="district_id" class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.district') }}
                        </label>
                        <select
                            id="district_id"
                            name="district_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            {{ $districts->isEmpty() ? 'disabled' : '' }}
                        >
                            <option value="">{{ __('admin.select_option') }}</option>
                            @foreach($districts as $district)
                                <option
                                    value="{{ $district->id }}"
                                    data-lat="{{ isset($district->lat) ? number_format($district->lat, 7, '.', '') : '' }}"
                                    data-lng="{{ isset($district->lng) ? number_format($district->lng, 7, '.', '') : '' }}"
                                    {{ (old('district_id', $project->district_id) == $district->id) ? 'selected' : '' }}
                                >
                                    {{ $district->name_local ?? $district->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('district_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- MAP SECTION -->
            <div class="pt-6 border-t border-gray-200">
                <x-location.map
                    :lat="$project->lat ?? 26.8206"
                    :lng="$project->lng ?? 30.8025"
                    :polygon="$project->boundary_geojson"
                    mapId="map-container"
                    entityLevel="project"
                    :entityId="$project->id"
                    :lockToEgypt="false"
                    inputLatName="lat"
                    inputLngName="lng"
                    inputPolygonName="boundary_geojson"
                    :autoInit="false"
                    :searchable="false"
                />
            </div>

        </div>

        <!-- Navigation Buttons -->
        <div class="mt-8 flex justify-between">
            <button
                type="button"
                class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 opacity-50 cursor-not-allowed"
                disabled
            >
                {{ __('admin.previous') }}
            </button>
            <button
                type="submit"
                class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                {{ __('admin.next') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- CASCADING DROPDOWNS ---
            const countrySelect = document.getElementById('country_id');
            const regionSelect = document.getElementById('region_id');
            const citySelect = document.getElementById('city_id');
            const districtSelect = document.getElementById('district_id');

            const locale = "{{ app()->getLocale() }}"; // 'ar' or 'en'
            let mapInstance;

            function clearSelect(select) {
                select.innerHTML = '<option value="">{{ __('admin.select_option') }}</option>';
                select.disabled = true;
            }

            function populateSelect(select, items) {
                select.disabled = false;
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.text = locale === 'ar' ? (item.name_local || item.name_en) : item.name_en;
                    // Ensure lat/lng are present (handle 0)
                    if(item.lat !== null && item.lat !== undefined && item.lng !== null && item.lng !== undefined) {
                        let latStr = String(item.lat).replace(',', '.');
                        let lngStr = String(item.lng).replace(',', '.');
                        option.dataset.lat = latStr;
                        option.dataset.lng = lngStr;
                    }
                    select.appendChild(option);
                });
            }

            // Dropdown Event Listeners for cascading logic ONLY
            // Map sync is handled by map.setupLocationDropdowns() below

            countrySelect.addEventListener('change', function() {
                const countryId = this.value;
                clearSelect(regionSelect);
                clearSelect(citySelect);
                clearSelect(districtSelect);

                if (countryId) {
                    fetch(`{{ url('admin/locations/regions') }}/${countryId}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.regions) populateSelect(regionSelect, data.regions);
                        });
                }
            });

            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                clearSelect(citySelect);
                clearSelect(districtSelect);

                if (regionId) {
                    fetch(`{{ url('admin/locations/cities') }}/${regionId}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.cities) populateSelect(citySelect, data.cities);
                        });
                }
            });

            citySelect.addEventListener('change', function() {
                const cityId = this.value;
                clearSelect(districtSelect);

                if (cityId) {
                    fetch(`{{ url('admin/locations/districts') }}/${cityId}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.districts) populateSelect(districtSelect, data.districts);
                        });
                }
            });

            // --- MAP INIT ---
            initLocationMap({
                elementId: 'map-container',
                entityLevel: 'project',
                entityId: {{ $project->id ?? 'null' }},
                polygonFieldSelector: '#boundary-map-container',
                latFieldSelector: '#lat-map-container',
                lngFieldSelector: '#lng-map-container',
                lat: {{ number_format($project->lat ?? 26.8206, 7, '.', '') }},
                lng: {{ number_format($project->lng ?? 30.8025, 7, '.', '') }},
                zoom: {{ $project->lat ? 13 : 6 }},
                lockToEgypt: false,
                apiPolygonUrl: '{{ route("admin.location-polygons") }}',
                onMapInit: function(map) {
                    mapInstance = map;

                    // 1. Setup automatic sync between dropdowns and map
                    map.setupLocationDropdowns({
                        country: countrySelect,
                        region: regionSelect,
                        city: citySelect,
                        district: districtSelect
                    });

                    // 2. Restore state if editing (show boundary)
                    const regionId = regionSelect.value;
                    const cityId = citySelect.value;
                    const districtId = districtSelect.value;

                    let activeLevel = null;
                    let activeId = null;
                    let activeSelect = null;
                    let activeZoom = 6;

                    if (districtId) {
                        activeLevel = 'district';
                        activeId = districtId;
                        activeSelect = districtSelect;
                        activeZoom = 13;
                    } else if (cityId) {
                        activeLevel = 'city';
                        activeId = cityId;
                        activeSelect = citySelect;
                        activeZoom = 11;
                    } else if (regionId) {
                        activeLevel = 'region';
                        activeId = regionId;
                        activeSelect = regionSelect;
                        activeZoom = 9;
                    }

                    if (activeLevel && activeId) {
                        // Fetch and show the boundary of the selected location
                        map.fetchAndShowBoundary(activeLevel, activeId);

                        // Decide whether to fly to location point or stay put
                        const projectHasCoords = {{ $project->lat ? 'true' : 'false' }};

                        if (!projectHasCoords && activeSelect && activeSelect.options[activeSelect.selectedIndex]) {
                             const option = activeSelect.options[activeSelect.selectedIndex];
                             const lat = option.dataset.lat;
                             const lng = option.dataset.lng;

                             if (lat && lng) {
                                 map.flyToLocation(lat, lng, activeZoom);
                             }
                        }
                    }
                },
                onPolygonChange: function(data) {
                     if (data.features.length > 0) {
                         document.getElementById('boundary-map-container').value = JSON.stringify(data);
                     } else {
                         document.getElementById('boundary-map-container').value = '';
                     }
                }
            });
        });
    </script>
@endpush
@endsection
