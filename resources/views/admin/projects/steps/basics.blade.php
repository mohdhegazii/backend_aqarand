@extends('admin.layouts.app')

@section('header')
    {{ $project->exists ? __('admin.edit_project') . ': ' . ($project->name_ar ?? $project->name_en) : __('admin.create_new') }}
@endsection

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <!-- Step Indicator -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">{{ __('admin.project_wizard.step_basics') }}</h2>
        <p class="text-sm text-gray-500">{{ __('admin.project_wizard.step_1_of_x') }}</p>
        <div class="mt-2 h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-indigo-600 rounded-full" style="width: 15%"></div>
        </div>
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
                <select
                    id="developer_id"
                    name="developer_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required
                >
                    <option value="">{{ __('admin.project_wizard.select_developer') }}</option>
                    @foreach($developers as $developer)
                        <option value="{{ $developer->id }}" {{ (old('developer_id', $project->developer_id) == $developer->id) ? 'selected' : '' }}>
                            {{ $developer->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('developer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- PROJECT LOCATION SECTION -->
            <div class="pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.projects.location_section') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Country (Fixed) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.projects.country_fixed_egypt') }}
                        </label>
                        <select disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 cursor-not-allowed shadow-sm sm:text-sm">
                            <option selected>{{ __('admin.projects.country_fixed_egypt') }}</option>
                        </select>
                        <!-- Implicit Country ID handling in Controller, or can send hidden -->
                    </div>

                    <!-- Region / Governorate -->
                    <div>
                        <label for="region_id" class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.projects.governorate') }} <span class="text-red-500">*</span>
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
                                    data-lat="{{ $region->lat }}"
                                    data-lng="{{ $region->lng }}"
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
                            {{ __('admin.projects.city') }} <span class="text-red-500">*</span>
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
                                    data-lat="{{ $city->lat }}"
                                    data-lng="{{ $city->lng }}"
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

                    <!-- District -->
                    <div>
                        <label for="district_id" class="block text-sm font-semibold text-gray-700">
                            {{ __('admin.projects.district') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="district_id"
                            name="district_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                            {{ $districts->isEmpty() ? 'disabled' : '' }}
                        >
                            <option value="">{{ __('admin.select_option') }}</option>
                            @foreach($districts as $district)
                                <option
                                    value="{{ $district->id }}"
                                    data-lat="{{ $district->lat }}"
                                    data-lng="{{ $district->lng }}"
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
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.projects.map_title') }}</h3>
                <p class="text-sm text-gray-500 mb-2">{{ __('admin.project_map_instruction') }}</p>

                <div id="map-container" class="w-full h-96 rounded-lg border border-gray-300 z-0"></div>

                <input type="hidden" name="boundary_geojson" id="boundary_geojson" value="{{ old('boundary_geojson', json_encode($project->boundary_geojson ?? null)) }}">
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

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- CASCADING DROPDOWNS ---
            const regionSelect = document.getElementById('region_id');
            const citySelect = document.getElementById('city_id');
            const districtSelect = document.getElementById('district_id');

            const locale = "{{ app()->getLocale() }}"; // 'ar' or 'en'

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
                    if(item.lat && item.lng) {
                        option.dataset.lat = item.lat;
                        option.dataset.lng = item.lng;
                    }
                    select.appendChild(option);
                });
            }

            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                clearSelect(citySelect);
                clearSelect(districtSelect);

                if (regionId) {
                    fetch(`{{ url('admin/locations/regions') }}/${regionId}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.cities) populateSelect(citySelect, data.cities);
                        });

                    // Map Fly To
                    const option = this.options[this.selectedIndex];
                    if(option.dataset.lat && option.dataset.lng) {
                        flyToLocation(option.dataset.lat, option.dataset.lng, 9);
                    }
                }
            });

            citySelect.addEventListener('change', function() {
                const cityId = this.value;
                clearSelect(districtSelect);

                if (cityId) {
                    fetch(`{{ url('admin/locations/cities') }}/${cityId}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.districts) populateSelect(districtSelect, data.districts);
                        });

                    // Map Fly To
                    const option = this.options[this.selectedIndex];
                    if(option.dataset.lat && option.dataset.lng) {
                        flyToLocation(option.dataset.lat, option.dataset.lng, 11);
                    }
                }
            });

            districtSelect.addEventListener('change', function() {
                // Map Fly To
                const option = this.options[this.selectedIndex];
                if(option.dataset.lat && option.dataset.lng) {
                    flyToLocation(option.dataset.lat, option.dataset.lng, 13);
                }
            });

            // --- MAP LOGIC ---
            // Egypt Bounds
            const southWest = L.latLng(22.0, 24.0);
            const northEast = L.latLng(32.0, 37.0);
            const bounds = L.latLngBounds(southWest, northEast);

            const map = L.map('map-container', {
                center: [26.8206, 30.8025], // Center of Egypt
                zoom: 6,
                maxBounds: bounds,
                maxBoundsViscosity: 1.0,
                minZoom: 5
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // FeatureGroup to store editable layers
            const drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            // Init existing polygon if any
            const existingGeoJsonInput = document.getElementById('boundary_geojson');
            if (existingGeoJsonInput.value && existingGeoJsonInput.value !== 'null' && existingGeoJsonInput.value !== '""') {
                try {
                    let geoJsonData = JSON.parse(existingGeoJsonInput.value);
                    // Handle double encoding if it happens
                    if(typeof geoJsonData === 'string') geoJsonData = JSON.parse(geoJsonData);

                    const geoJsonLayer = L.geoJSON(geoJsonData);
                    geoJsonLayer.eachLayer(function(layer) {
                        drawnItems.addLayer(layer);
                    });
                    if(drawnItems.getLayers().length > 0) {
                        map.fitBounds(drawnItems.getBounds());
                    }
                } catch(e) {
                    console.error("Error parsing GeoJSON", e);
                }
            }

            // Init Draw Control
            const drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });
            map.addControl(drawControl);

            function updateHiddenInput() {
                const data = drawnItems.toGeoJSON();
                // We only want the features, or specifically the first polygon if we enforce single
                if (data.features.length > 0) {
                     // Save as FeatureCollection or just the Geometry?
                     // Usually FeatureCollection is safer standard.
                     document.getElementById('boundary_geojson').value = JSON.stringify(data);
                } else {
                    document.getElementById('boundary_geojson').value = '';
                }
            }

            map.on(L.Draw.Event.CREATED, function (e) {
                // Remove existing layers to enforce single polygon if needed?
                // For now allow multiple shapes or single? Requirement: "project boundary". usually single.
                drawnItems.clearLayers(); // Clear previous

                const layer = e.layer;
                drawnItems.addLayer(layer);
                updateHiddenInput();
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                updateHiddenInput();
            });

            map.on(L.Draw.Event.DELETED, function (e) {
                updateHiddenInput();
            });

            function flyToLocation(lat, lng, zoom) {
                map.flyTo([lat, lng], zoom);
            }
        });
    </script>
@endpush
@endsection
