@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' ' . __('admin.projects'))

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.projects.store') }}">
            @csrf

            <!-- Common Fields -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">@lang('admin.details')</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.amenities')</label>
                        <select name="amenities[]" id="amenities" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-24">
                            @foreach($amenities as $amenity)
                                <option value="{{ $amenity->id }}" {{ collect(old('amenities'))->contains($amenity->id) ? 'selected' : '' }}>
                                    {{ $amenity->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">@lang('admin.location')</h3>

                <!-- Search -->
                <div class="mb-4 relative">
                    <label class="block text-sm font-medium text-gray-700">@lang('admin.search_location')</label>
                    <input type="text" id="location-search" placeholder="Search Country, City, District..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <ul id="location-results" class="absolute z-10 bg-white border border-gray-300 w-full max-h-60 overflow-y-auto hidden rounded-md shadow-lg mt-1"></ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Hierarchy -->
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
                        <label for="district_id" class="block text-sm font-medium text-gray-700">@lang('admin.district') *</label>
                        <select name="district_id" id="district_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">@lang('admin.select_option')</option>
                        </select>
                    </div>

                    <!-- Lat/Lng Inputs (Readonly or Editable) -->
                    <div>
                        <label for="lat" class="block text-sm font-medium text-gray-700">@lang('admin.lat')</label>
                        <input type="text" name="lat" id="lat" value="{{ old('lat') }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>
                    <div>
                        <label for="lng" class="block text-sm font-medium text-gray-700">@lang('admin.lng')</label>
                        <input type="text" name="lng" id="lng" value="{{ old('lng') }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>
                </div>

                <!-- Map -->
                <div id="map" class="h-96 w-full rounded-lg border border-gray-300 z-0"></div>
                <p class="text-xs text-gray-500 mt-2">@lang('admin.location_on_map') - Click to set coordinates.</p>
            </div>

            <!-- Status (Calculated/Read-only usually, but manual override allowed per prompt?) -->
            <!-- The prompt says: "Status is from units...". So for CREATE, it's irrelevant or default 'New Launch'. -->
            <!-- We will hide it or make it disabled. Let's make it a hidden field default to 'new_launch' for now,
                 or show it disabled.
                 But DB requires it. We set default in migration.
                 Let's show it as disabled info. -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Delivery</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status (Auto-calculated from Units)</label>
                        <input type="text" value="New Launch" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 shadow-sm">
                        <input type="hidden" name="status" value="new_launch">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Delivery Year (Auto-calculated)</label>
                        <input type="text" value="-" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 shadow-sm">
                        <!-- Allow override? prompt says "taken from units". -->
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div x-data="{ tab: 'en' }">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 rtl:space-x-reverse" aria-label="Tabs">
                        <button type="button" @click="tab = 'en'" :class="{ 'border-blue-500 text-blue-600': tab === 'en', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'en' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            English
                        </button>
                        <button type="button" @click="tab = 'ar'" :class="{ 'border-blue-500 text-blue-600': tab === 'ar', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'ar' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Arabic
                        </button>
                    </nav>
                </div>

                <!-- English Tab -->
                <div x-show="tab === 'en'" class="py-4">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name_en" class="block text-sm font-medium text-gray-700">Name (EN) *</label>
                            <input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>
                        <div>
                            <label for="tagline_en" class="block text-sm font-medium text-gray-700">Tagline (EN)</label>
                            <input type="text" name="tagline_en" id="tagline_en" value="{{ old('tagline_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="address_en" class="block text-sm font-medium text-gray-700">Address (EN)</label>
                            <input type="text" name="address_en" id="address_en" value="{{ old('address_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="description_en" class="block text-sm font-medium text-gray-700">Description (EN)</label>
                            <textarea name="description_en" id="description_en" class="wysiwyg">{{ old('description_en') }}</textarea>
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (English)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug (EN)</label>
                                    <input type="text" name="seo_slug_en" value="{{ old('seo_slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meta Title (EN)</label>
                                    <input type="text" name="meta_title_en" value="{{ old('meta_title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Meta Description (EN)</label>
                                    <textarea name="meta_description_en" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('meta_description_en') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arabic Tab -->
                <div x-show="tab === 'ar'" class="py-4" style="display: none;">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name_ar" class="block text-sm font-medium text-gray-700">Name (AR)</label>
                            <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="tagline_ar" class="block text-sm font-medium text-gray-700">Tagline (AR)</label>
                            <input type="text" name="tagline_ar" id="tagline_ar" value="{{ old('tagline_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="address_ar" class="block text-sm font-medium text-gray-700">Address (AR)</label>
                            <input type="text" name="address_ar" id="address_ar" value="{{ old('address_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="description_ar" class="block text-sm font-medium text-gray-700">Description (AR)</label>
                            <textarea name="description_ar" id="description_ar" class="wysiwyg">{{ old('description_ar') }}</textarea>
                        </div>

                         <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (Arabic)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug (AR)</label>
                                    <input type="text" name="seo_slug_ar" value="{{ old('seo_slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meta Title (AR)</label>
                                    <input type="text" name="meta_title_ar" value="{{ old('meta_title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Meta Description (AR)</label>
                                    <textarea name="meta_description_ar" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('meta_description_ar') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center mb-6 mt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">@lang('admin.is_active')</label>
            </div>

            <div class="flex justify-end">
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

<!-- Alpine.js for Tabs (Lightweight) -->
<script src="//unpkg.com/alpinejs" defer></script>

<script>
    // --- Map Logic ---
    let map, marker;
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        map = L.map('map').setView([30.0444, 31.2357], 6); // Default Cairo
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Click Event
        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });
    });

    function updateMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        document.getElementById('lat').value = lat.toFixed(7);
        document.getElementById('lng').value = lng.toFixed(7);
    }

    // --- Search Logic ---
    const searchInput = document.getElementById('location-search');
    const resultsList = document.getElementById('location-results');

    searchInput.addEventListener('input', function() {
        const query = this.value;
        if (query.length < 2) {
            resultsList.classList.add('hidden');
            return;
        }

        fetch(`/admin/locations/global-search?q=${query}`)
            .then(res => res.json())
            .then(data => {
                resultsList.innerHTML = '';
                if (data.length > 0) {
                    resultsList.classList.remove('hidden');
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item.name;
                        li.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                        li.onclick = () => selectLocation(item);
                        resultsList.appendChild(li);
                    });
                } else {
                    resultsList.classList.add('hidden');
                }
            });
    });

    function selectLocation(item) {
        searchInput.value = item.name;
        resultsList.classList.add('hidden');

        // Auto-select dropdowns
        if (item.country_id) {
            document.getElementById('country_id').value = item.country_id;
            fetchRegions(item.country_id, item.type === 'region' ? item.id : item.region_id);
        }

        // Wait for fetch, or handle async... simplest is to trigger change
        // We need chain: Region -> City -> District
        // This is tricky with async fetches.
        // We will implement a custom chain loader for selection

        // For now, simpler approach: trigger hierarchical load
        if (item.country_id) {
             // Load Regions
             fetch(`/admin/locations/countries/${item.country_id}`)
                .then(r => r.json())
                .then(d => {
                    populateSelect('region_id', d.regions);

                    if (item.region_id || (item.type === 'region')) {
                        const rId = item.type === 'region' ? item.id : item.region_id;
                        document.getElementById('region_id').value = rId;

                        // Load Cities
                         fetch(`/admin/locations/regions/${rId}`)
                            .then(r => r.json())
                            .then(d => {
                                populateSelect('city_id', d.cities);

                                if (item.city_id || (item.type === 'city')) {
                                     const cId = item.type === 'city' ? item.id : item.city_id;
                                     document.getElementById('city_id').value = cId;

                                     // Load Districts
                                      fetch(`/admin/locations/cities/${cId}`)
                                        .then(r => r.json())
                                        .then(d => {
                                            populateSelect('district_id', d.districts);
                                            if (item.type === 'district') {
                                                document.getElementById('district_id').value = item.id;
                                            }
                                        });
                                } else {
                                     resetSelect('district_id');
                                }
                            });
                    } else {
                        resetSelect('city_id');
                        resetSelect('district_id');
                    }
                });
        }
    }

    // --- Dropdown Logic (Existing but refactored helper) ---
    function populateSelect(id, items) {
        const select = document.getElementById(id);
        select.innerHTML = '<option value="">@lang("admin.select_option")</option>';
        items.forEach(i => {
             select.innerHTML += `<option value="${i.id}">${i.name_en}</option>`;
        });
    }

    function resetSelect(id) {
        document.getElementById(id).innerHTML = '<option value="">@lang("admin.select_option")</option>';
    }

    function fetchRegions(countryId, selectedId = null) {
        if (!countryId) { resetSelect('region_id'); resetSelect('city_id'); resetSelect('district_id'); return; }
        fetch(`/admin/locations/countries/${countryId}`)
            .then(response => response.json())
            .then(data => {
                populateSelect('region_id', data.regions);
                if(selectedId) document.getElementById('region_id').value = selectedId;
            });
    }

    function fetchCities(regionId, selectedId = null) {
        if (!regionId) { resetSelect('city_id'); resetSelect('district_id'); return; }
        fetch(`/admin/locations/regions/${regionId}`)
            .then(response => response.json())
            .then(data => {
                populateSelect('city_id', data.cities);
                if(selectedId) document.getElementById('city_id').value = selectedId;
            });
    }

    function fetchDistricts(cityId, selectedId = null) {
        if (!cityId) { resetSelect('district_id'); return; }
        fetch(`/admin/locations/cities/${cityId}`)
            .then(response => response.json())
            .then(data => {
                populateSelect('district_id', data.districts);
                 if(selectedId) document.getElementById('district_id').value = selectedId;
            });
    }
</script>
@endsection
