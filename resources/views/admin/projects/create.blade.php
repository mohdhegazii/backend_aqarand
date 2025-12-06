@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' ' . __('admin.projects'))

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.projects.store') }}" id="projectForm" enctype="multipart/form-data">
            @csrf

            <!-- =========================
                 Common Section (Top)
                 ========================= -->
            <div class="mb-8 border-b pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">@lang('admin.common_info')</h3>

                <!-- Unified Location Search -->
                <div class="mb-6 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('admin.location_search')</label>
                    <input type="text" id="locationSearch" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Search Country, Region, City, District...">
                    <div id="searchResults" class="absolute z-50 bg-white shadow-lg w-full mt-1 rounded-md hidden max-h-60 overflow-y-auto border border-gray-200"></div>
                </div>

                <!-- Cascading Dropdowns -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label for="country_id" class="block text-sm font-medium text-gray-700">@lang('admin.country') *</label>
                        <select name="country_id" id="country_id" onchange="fetchRegions(this.value)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">@lang('admin.select_option')</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="region_id" class="block text-sm font-medium text-gray-700">@lang('admin.region') *</label>
                        <select name="region_id" id="region_id" onchange="fetchCities(this.value)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">@lang('admin.select_option')</option>
                        </select>
                    </div>
                    <div>
                        <label for="city_id" class="block text-sm font-medium text-gray-700">@lang('admin.city') *</label>
                        <select name="city_id" id="city_id" onchange="fetchDistricts(this.value)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">@lang('admin.select_option')</option>
                        </select>
                    </div>
                    <div>
                        <label for="district_id" class="block text-sm font-medium text-gray-700">@lang('admin.district')</label>
                        <select name="district_id" id="district_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">@lang('admin.select_option')</option>
                        </select>
                    </div>
                </div>

                <!-- Map & Coordinates -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="lg:col-span-2">
                         <label class="block text-sm font-medium text-gray-700 mb-2">@lang('admin.map') (Click to set coordinates)</label>
                         <div id="map" class="w-full h-80 bg-gray-200 rounded-md border"></div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="lat" class="block text-sm font-medium text-gray-700">@lang('admin.lat')</label>
                            <input type="text" name="lat" id="lat" value="{{ old('lat') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="lng" class="block text-sm font-medium text-gray-700">@lang('admin.lng')</label>
                            <input type="text" name="lng" id="lng" value="{{ old('lng') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>
                </div>

                <!-- Basic Common Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="developer_id" class="block text-sm font-medium text-gray-700">@lang('admin.developer')</label>
                        <select name="developer_id" id="developer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">@lang('admin.select_option')</option>
                            @foreach($developers as $developer)
                                <option value="{{ $developer->id }}" {{ old('developer_id') == $developer->id ? 'selected' : '' }}>
                                    {{ $developer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                         <!-- Status is Read-Only / Derived -->
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.status') (Derived from Units)</label>
                        <input type="text" value="New Launch" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed shadow-sm">
                        <input type="hidden" name="status" value="new_launch">
                    </div>
                    <div>
                        <label for="amenities" class="block text-sm font-medium text-gray-700">@lang('admin.amenities')</label>
                        <select name="amenities[]" id="amenities" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-32">
                            @foreach($amenities as $amenity)
                                <option value="{{ $amenity->id }}" {{ collect(old('amenities'))->contains($amenity->id) ? 'selected' : '' }}>
                                    {{ $amenity->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                     <div class="flex items-center mt-6">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">@lang('admin.is_active')</label>
                    </div>
                </div>

                 <!-- =========================
                     Media & Brochure Section
                     ========================= -->
                <div class="mb-8 border-b pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Media & Brochure</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Hero Image (Selection Note) -->
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700">Hero Image</label>
                            <div class="p-3 bg-gray-50 rounded text-sm text-gray-600 border">
                                @lang('admin.hero_image_note', ['default' => 'The first uploaded gallery image will be automatically set as the Hero Image. You can change this later in the Edit screen.'])
                            </div>
                        </div>

                        <!-- Brochure -->
                        <div class="mb-3">
                            <label for="brochure" class="block text-sm font-medium text-gray-700">Project Brochure (PDF)</label>
                             <input type="file" name="brochure" id="brochure" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100">
                            <small class="text-muted text-xs">PDF only, max 5MB</small>
                            @error('brochure') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Gallery -->
                    <div class="mt-4">
                        <label for="gallery" class="block text-sm font-medium text-gray-700">Gallery Images</label>
                        <input type="file" name="gallery[]" id="gallery" multiple class="mt-1 block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100">
                        <small class="text-muted text-xs">Multiple images allowed, JPEG/PNG/WebP, max 4MB per file. Will be resized to 1600px.</small>
                        @error('gallery') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
                        @error('gallery.*') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- SEO Keywords -->
                <div class="bg-gray-50 p-4 rounded mb-6">
                    <h4 class="font-medium text-gray-700 mb-2">SEO Keywords</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                             <label for="main_keyword_en" class="block text-sm font-medium text-gray-700">Main Keyword (EN)</label>
                             <input type="text" name="main_keyword_en" id="main_keyword_en" value="{{ old('main_keyword_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                             <label for="secondary_keywords_en_str" class="block text-sm font-medium text-gray-700 mt-2">Secondary Keywords (EN) (Comma separated)</label>
                             <input type="text" name="secondary_keywords_en_str" id="secondary_keywords_en_str" value="{{ old('secondary_keywords_en_str') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                             <label for="main_keyword_ar" class="block text-sm font-medium text-gray-700">Main Keyword (AR)</label>
                             <input type="text" name="main_keyword_ar" id="main_keyword_ar" value="{{ old('main_keyword_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                             <label for="secondary_keywords_ar_str" class="block text-sm font-medium text-gray-700 mt-2">Secondary Keywords (AR) (Comma separated)</label>
                             <input type="text" name="secondary_keywords_ar_str" id="secondary_keywords_ar_str" value="{{ old('secondary_keywords_ar_str') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

            </div>

            <!-- =========================
                 Tabs Section (Languages)
                 ========================= -->
            <div x-data="{ activeTab: 'en' }">
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'en'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'en', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'en' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            English
                        </button>
                        <button type="button" @click="activeTab = 'ar'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'ar', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'ar' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Arabic
                        </button>
                    </nav>
                </div>

                <!-- English Content -->
                <div x-show="activeTab === 'en'" class="space-y-6">
                    <div>
                        <label for="name_en" class="block text-sm font-medium text-gray-700">@lang('admin.name_en') *</label>
                        <input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="tagline_en" class="block text-sm font-medium text-gray-700">Tagline (EN)</label>
                        <input type="text" name="tagline_en" id="tagline_en" value="{{ old('tagline_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="description_en" class="block text-sm font-medium text-gray-700">Description (EN)</label>
                        <textarea name="description_en" id="description_en" class="wysiwyg mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description_en') }}</textarea>
                    </div>
                    <div>
                        <label for="seo_slug_en" class="block text-sm font-medium text-gray-700">SEO Slug (EN) (Auto-generated)</label>
                        <input type="text" name="seo_slug_en" id="seo_slug_en" value="{{ old('seo_slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="meta_title_en" class="block text-sm font-medium text-gray-700">Meta Title (EN)</label>
                        <input type="text" name="meta_title_en" id="meta_title_en" value="{{ old('meta_title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="meta_description_en" class="block text-sm font-medium text-gray-700">Meta Description (EN)</label>
                        <textarea name="meta_description_en" id="meta_description_en" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('meta_description_en') }}</textarea>
                    </div>
                </div>

                <!-- Arabic Content -->
                <div x-show="activeTab === 'ar'" class="space-y-6" style="display: none;">
                    <div>
                        <label for="name_ar" class="block text-sm font-medium text-gray-700">@lang('admin.name_ar')</label>
                        <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="tagline_ar" class="block text-sm font-medium text-gray-700">Tagline (AR)</label>
                        <input type="text" name="tagline_ar" id="tagline_ar" value="{{ old('tagline_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="description_ar" class="block text-sm font-medium text-gray-700">Description (AR)</label>
                        <textarea name="description_ar" id="description_ar" class="wysiwyg mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description_ar') }}</textarea>
                    </div>
                    <div>
                        <label for="seo_slug_ar" class="block text-sm font-medium text-gray-700">SEO Slug (AR) (Auto-generated)</label>
                        <input type="text" name="seo_slug_ar" id="seo_slug_ar" value="{{ old('seo_slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                     <div>
                        <label for="meta_title_ar" class="block text-sm font-medium text-gray-700">Meta Title (AR)</label>
                        <input type="text" name="meta_title_ar" id="meta_title_ar" value="{{ old('meta_title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="meta_description_ar" class="block text-sm font-medium text-gray-700">Meta Description (AR)</label>
                        <textarea name="meta_description_ar" id="meta_description_ar" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('meta_description_ar') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <a href="{{ route('admin.projects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                    @lang('admin.cancel')
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    @lang('admin.save')
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.tiny.cloud/1/qgkphze48t0eieue0gqhegrc25hz9hyt75xojlt84f36f9md/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // TinyMCE
    tinymce.init({
        selector: '.wysiwyg',
        plugins: 'table lists link image preview',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | table link image | preview',
        height: 300,
        menubar: false
    });

    // Map
    let map, marker;
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        map = L.map('map').setView([30.0444, 31.2357], 6); // Default Cairo
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(7);
            const lng = e.latlng.lng.toFixed(7);

            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;

            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);
        });

        // Bi-directional binding: If user manually changes inputs, update map
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');

        function updateMapFromInputs() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);

            if (!isNaN(lat) && !isNaN(lng)) {
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
                map.setView([lat, lng], 14);
            }
        }

        latInput.addEventListener('change', updateMapFromInputs);
        lngInput.addEventListener('change', updateMapFromInputs);
    });

    // Location Search Logic
    const searchInput = document.getElementById('locationSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value;
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.locations.search') }}?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        searchResults.classList.remove('hidden');
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'p-2 hover:bg-gray-100 cursor-pointer border-b last:border-b-0';
                            div.innerHTML = `<span class="font-bold text-xs uppercase text-gray-500 mr-2">[${item.type}]</span> ${item.name}`;
                            div.onclick = () => selectLocation(item);
                            searchResults.appendChild(div);
                        });
                    } else {
                        searchResults.classList.add('hidden');
                    }
                });
        }, 300);
    });

    function selectLocation(item) {
        searchInput.value = item.name;
        searchResults.classList.add('hidden');

        // Auto-fill and Trigger Changes
        const d = item.data;

        // 1. Set Country
        document.getElementById('country_id').value = d.country_id;
        fetchRegions(d.country_id, () => {
            // 2. Set Region
             document.getElementById('region_id').value = d.region_id;
             // Disable country/region if lower level selected?
             // User requirement: "If governorate selected, fields below empty... if district selected, all filled"
             // My logic: If I select a District, I fill Country, Region, City, District.

             if (d.region_id) {
                 fetchCities(d.region_id, () => {
                     if (d.city_id) {
                         document.getElementById('city_id').value = d.city_id;
                         fetchDistricts(d.city_id, () => {
                             if (d.district_id) {
                                 document.getElementById('district_id').value = d.district_id;
                             } else {
                                 document.getElementById('district_id').value = "";
                             }
                         });
                     } else {
                         document.getElementById('city_id').value = "";
                         document.getElementById('district_id').innerHTML = '<option value="">@lang("admin.select_option")</option>';
                     }
                 });
             }
        });

        // Fly To Location (Using generic coords if available or just by zoom - since we don't have coords in search result yet, we might skip or fetch them)
        // ideally the search endpoint returns coords. For now we skip flying unless we add lat/lng to search response.
    }

    // Standard Dropdown Logic
    function fetchRegions(countryId, callback) {
        const regionSelect = document.getElementById('region_id');
        const citySelect = document.getElementById('city_id');
        const districtSelect = document.getElementById('district_id');

        // Clear children
        regionSelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';
        citySelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';
        districtSelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';

        if (!countryId) return;

        // FlyTo Logic for Country (Optional: we can fetch country coords if needed)
        // For now, let's just fetch regions.

        fetch(`/admin/locations/countries/${countryId}`)
            .then(response => response.json())
            .then(data => {
                // Determine which name to show based on current locale
                const isRtl = document.documentElement.dir === 'rtl';

                data.regions.forEach(region => {
                    const name = isRtl ? (region.name_local || region.name_ar || region.name_en) : region.name_en;
                    regionSelect.innerHTML += `<option value="${region.id}" data-lat="${region.lat}" data-lng="${region.lng}">${name}</option>`;
                });
                if (callback) callback();
            });
    }

    function fetchCities(regionId, callback) {
        const citySelect = document.getElementById('city_id');
        const districtSelect = document.getElementById('district_id');

        // Clear children
        citySelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';
        districtSelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';

        if (!regionId) return;

        // FlyTo Region
        const regionSelect = document.getElementById('region_id');
        const selectedOption = regionSelect.options[regionSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.lat && selectedOption.dataset.lng) {
            flyToLocation(selectedOption.dataset.lat, selectedOption.dataset.lng);
        }

        fetch(`/admin/locations/regions/${regionId}`)
            .then(response => response.json())
            .then(data => {
                const isRtl = document.documentElement.dir === 'rtl';
                data.cities.forEach(city => {
                    const name = isRtl ? (city.name_local || city.name_ar || city.name_en) : city.name_en;
                    citySelect.innerHTML += `<option value="${city.id}" data-lat="${city.lat}" data-lng="${city.lng}">${name}</option>`;
                });
                 if (callback) callback();
            });
    }

    function fetchDistricts(cityId, callback) {
        const districtSelect = document.getElementById('district_id');
        districtSelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';

        if (!cityId) return;

        // FlyTo City
        const citySelect = document.getElementById('city_id');
        const selectedOption = citySelect.options[citySelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.lat && selectedOption.dataset.lng) {
            flyToLocation(selectedOption.dataset.lat, selectedOption.dataset.lng);
        }

        fetch(`/admin/locations/cities/${cityId}`)
            .then(response => response.json())
            .then(data => {
                const isRtl = document.documentElement.dir === 'rtl';
                data.districts.forEach(district => {
                    const name = isRtl ? (district.name_local || district.name_ar || district.name_en) : district.name_en;
                    districtSelect.innerHTML += `<option value="${district.id}" data-lat="${district.lat}" data-lng="${district.lng}">${name}</option>`;
                });
                 if (callback) callback();
            });

        // Also handle District selection to fly there
        districtSelect.onchange = function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.lat && selectedOption.dataset.lng) {
                flyToLocation(selectedOption.dataset.lat, selectedOption.dataset.lng);
            }
        };
    }

    function flyToLocation(lat, lng) {
        if (lat && lng && map) {
            map.flyTo([lat, lng], 12); // Zoom level 12 for regions/cities
            // Don't move the marker yet unless they click. Or should we?
            // The requirement says "fly to the point we select".
            // Usually in admin, if I select a city, I want the map to center there so I can place the marker accurately.
            // I won't move the marker automatically to the center of the city because the marker represents the PROJECT location.
        }
    }

    // Close search results on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
</script>
@endsection
