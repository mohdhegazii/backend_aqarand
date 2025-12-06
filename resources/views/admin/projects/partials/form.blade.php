@php
    $isEdit = isset($project);
    $gallery = $isEdit && $project->gallery ? $project->gallery : [];
    // Ensure gallery is array
    if (!is_array($gallery)) $gallery = [];
@endphp

<div x-data="projectForm({{ $isEdit ? 'true' : 'false' }}, {{ $isEdit ? $project->id : 'null' }})"
     x-init="initMap()"
     class="bg-white rounded-lg shadow-md p-6">

    <!-- Steps Navigation -->
    <div class="mb-8 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
            @foreach([__('admin.step_basic_info'), __('admin.step_details'), __('admin.step_description'), __('admin.step_media'), __('admin.step_publish')] as $index => $label)
            <li class="mr-2" role="presentation">
                <button type="button"
                        class="inline-block p-4 rounded-t-lg border-b-2"
                        :class="step === {{ $index + 1 }} ? 'border-blue-600 text-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'"
                        @click="goToStep({{ $index + 1 }})">
                    {{ $label }}
                </button>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- Form -->
    <form action="{{ $isEdit ? route('admin.projects.update', $project->id) : route('admin.projects.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="project-form">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">{{ __('admin.correct_errors') }}</strong>
                <ul class="mt-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- STEP 1: Basic Info & Location -->
        <div x-show="step === 1" class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">{{ __('admin.basic_info') }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name AR -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_name_ar') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $project->name_ar ?? '') }}" required class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <!-- Name EN -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_name_en') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name_en" value="{{ old('name_en', $project->name_en ?? '') }}" required class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <!-- Developer -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.developer') }}</label>
                    <select name="developer_id" class="w-full rounded border-gray-300 p-2">
                        <option value="">{{ __('admin.select_developer') }}</option>
                        @foreach($developers as $dev)
                            <option value="{{ $dev->id }}" {{ old('developer_id', $project->developer_id ?? '') == $dev->id ? 'selected' : '' }}>
                                {{ $dev->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Tagline (optional) -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.tagline') }}</label>
                    <input type="text" name="tagline_ar" value="{{ old('tagline_ar', $project->tagline_ar ?? '') }}" class="w-full rounded border-gray-300 p-2">
                </div>
            </div>

            <!-- Unified Search & Location -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200 mt-6">
                <h4 class="font-bold text-gray-700 mb-4">{{ __('admin.project_location') }}</h4>

                <!-- Search Input -->
                <div class="mb-4 relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('admin.unified_search') }}</label>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.500ms="performSearch"
                           placeholder="{{ __('admin.search_placeholder') }}"
                           class="w-full rounded border-gray-300 p-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                    <!-- Dropdown Results -->
                    <div x-show="searchResults.length > 0" class="absolute z-50 bg-white border border-gray-200 w-full mt-1 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                        <template x-for="result in searchResults" :key="result.id">
                            <div @click="selectSearchResult(result)" class="p-2 hover:bg-gray-100 cursor-pointer border-b text-sm">
                                <span x-text="result.name" class="font-medium"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Cascading Dropdowns (Hidden until selection) -->
                <div x-show="showCascading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 transition-all duration-300">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.country') }} <span class="text-red-500">*</span></label>
                        <select name="country_id" x-model="selectedCountry" @change="fetchRegions()" required class="w-full rounded border-gray-300 p-2 text-sm">
                            <option value="">{{ __('admin.select_country') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name_local ?? $country->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.region') }} <span class="text-red-500">*</span></label>
                        <select name="region_id" x-model="selectedRegion" @change="fetchCities()" required class="w-full rounded border-gray-300 p-2 text-sm">
                            <option value="">{{ __('admin.select_region') }}</option>
                            <template x-for="region in regions" :key="region.id">
                                <option :value="region.id" x-text="region.name_local || region.name_en"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.city') }} <span class="text-red-500">*</span></label>
                        <select name="city_id" x-model="selectedCity" @change="fetchDistricts()" required class="w-full rounded border-gray-300 p-2 text-sm">
                            <option value="">{{ __('admin.select_city') }}</option>
                            <template x-for="city in cities" :key="city.id">
                                <option :value="city.id" x-text="city.name_local || city.name_en"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.district') }}</label>
                        <select name="district_id" x-model="selectedDistrict" class="w-full rounded border-gray-300 p-2 text-sm">
                            <option value="">{{ __('admin.select_district') }}</option>
                            <template x-for="district in districts" :key="district.id">
                                <option :value="district.id" x-text="district.name_local || district.name_en"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="mt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('admin.project_map') }}</h3>
                <div id="project_map" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;" class="border border-gray-300"></div>

                <input type="hidden" name="map_polygon" id="map_polygon" value="{{ old('map_polygon', json_encode($project->map_polygon ?? null)) }}">
                <input type="hidden" name="lat" id="lat" value="{{ old('lat', $project->lat ?? '') }}">
                <input type="hidden" name="lng" id="lng" value="{{ old('lng', $project->lng ?? '') }}">

                <p class="text-xs text-gray-500 mt-1">{{ __('admin.map_instruction') }}</p>
            </div>
        </div>

        <!-- STEP 2: Details -->
        <div x-show="step === 2" class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">{{ __('admin.property_details_price') }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Prices -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.min_price') }}</label>
                    <input type="number" name="min_price" value="{{ old('min_price', $project->min_price ?? '') }}" class="w-full rounded border-gray-300 p-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.max_price') }}</label>
                    <input type="number" name="max_price" value="{{ old('max_price', $project->max_price ?? '') }}" class="w-full rounded border-gray-300 p-2">
                </div>

                <!-- Areas -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.min_area') }}</label>
                    <input type="number" name="min_bua" value="{{ old('min_bua', $project->min_bua ?? '') }}" class="w-full rounded border-gray-300 p-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.max_area') }}</label>
                    <input type="number" name="max_bua" value="{{ old('max_bua', $project->max_bua ?? '') }}" class="w-full rounded border-gray-300 p-2">
                </div>

                <!-- Total Units -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.total_units') }}</label>
                    <input type="number" name="total_units" value="{{ old('total_units', $project->total_units ?? '') }}" class="w-full rounded border-gray-300 p-2">
                </div>
            </div>

            <!-- Amenities -->
            <div class="mt-4">
                <label class="block text-gray-700 font-bold mb-2">{{ __('admin.amenities') }}</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded border">
                    @php
                        $selectedAmenities = $isEdit ? $project->amenities->pluck('id')->toArray() : [];
                    @endphp
                    @foreach($amenities as $amenity)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                   {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm">{{ $amenity->name_local ?? $amenity->name_en }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- STEP 3: Description -->
        <div x-show="step === 3" class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">{{ __('admin.step_description') }}</h3>

            <div>
                <label class="block text-gray-700 font-bold mb-2">{{ __('admin.description_detailed') }}</label>
                <textarea name="description_long" rows="10" class="w-full rounded border-gray-300 p-2">{{ old('description_long', $project->description_long ?? '') }}</textarea>
            </div>

            <div class="mt-6">
                <h4 class="font-bold text-gray-700 mb-2">{{ __('admin.seo_settings') }}</h4>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm mb-1">{{ __('admin.meta_title') }}</label>
                        <input type="text" name="meta_title_ar" value="{{ old('meta_title_ar', $project->meta_title_ar ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm mb-1">{{ __('admin.meta_description') }}</label>
                        <textarea name="meta_description_ar" rows="3" class="w-full rounded border-gray-300 p-2">{{ old('meta_description_ar', $project->meta_description_ar ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4: Media -->
        <div x-show="step === 4" class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">{{ __('admin.videos_photos') }}</h3>

            <!-- Hero Image -->
            <div class="bg-blue-50 p-4 rounded border border-blue-200">
                <label class="block text-blue-800 font-bold mb-2">{{ __('admin.hero_image') }}</label>
                @if($isEdit && $project->hero_image_url)
                    <div class="mb-2">
                        <img src="{{ Storage::url($project->hero_image_url) }}" class="h-40 w-auto object-cover rounded shadow">
                        <p class="text-xs text-gray-500 mt-1">{{ __('admin.current_image') }}</p>
                    </div>
                @endif
                <input type="file" name="hero_image" accept="image/*" {{ $isEdit ? '' : 'required' }} class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">{{ __('admin.hero_image_help') }}</p>
            </div>

            <!-- Video URL -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-2">{{ __('admin.video_url') }}</label>
                <input type="url" name="video_url" value="{{ old('video_url', $project->video_url ?? '') }}" class="w-full rounded border-gray-300 p-2" placeholder="https://youtube.com/...">
            </div>

            <!-- Gallery Grid -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-4">{{ __('admin.gallery') }}</label>

                <!-- Existing Images -->
                @if(count($gallery) > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    @foreach($gallery as $idx => $img)
                    <div class="border rounded bg-white p-2 relative" id="gallery-item-{{ $idx }}">
                        <img src="{{ Storage::url($img['path']) }}" class="w-full h-32 object-cover rounded mb-2">

                        <!-- Inputs -->
                        <div class="space-y-2">
                            <input type="hidden" name="gallery_data[{{ $idx }}][path]" value="{{ $img['path'] }}">
                            <input type="text" name="gallery_data[{{ $idx }}][name]" value="{{ $img['name'] ?? '' }}" placeholder="{{ __('admin.image_name') }}" class="w-full text-xs p-1 border rounded">
                            <input type="text" name="gallery_data[{{ $idx }}][alt]" value="{{ $img['alt'] ?? '' }}" placeholder="{{ __('admin.alt_text') }}" class="w-full text-xs p-1 border rounded">

                            <label class="flex items-center space-x-2 text-xs cursor-pointer">
                                <input type="radio" name="selected_hero" value="{{ $img['path'] }}"
                                       {{ ($project->hero_image_url ?? '') == $img['path'] ? 'checked' : '' }}
                                       class="text-blue-600">
                                <span class="mr-1">{{ __('admin.set_as_hero') }}</span>
                            </label>
                        </div>

                        <!-- Delete -->
                        <button type="button" onclick="document.getElementById('gallery-item-{{ $idx }}').remove()" class="absolute top-1 left-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700" title="{{ __('admin.delete_image') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Upload New -->
                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('admin.add_new_images') }}</label>
                    <input type="file" name="gallery[]" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.gallery_help') }}</p>
                </div>
            </div>

            <!-- Brochure -->
            <div class="bg-green-50 p-4 rounded border border-green-200">
                <label class="block text-green-800 font-bold mb-2">{{ __('admin.brochure') }}</label>
                @if($isEdit && $project->brochure_url)
                    <div class="mb-2 flex items-center">
                        <svg class="w-6 h-6 text-red-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <a href="{{ route('admin.media.download', ['path' => $project->brochure_url]) }}" target="_blank" class="text-blue-600 hover:underline text-sm">{{ __('admin.view_current_brochure') }}</a>
                    </div>
                @endif
                <input type="file" name="brochure" accept="application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <p class="text-xs text-gray-500 mt-1">{{ __('admin.brochure_help') }}</p>
            </div>
        </div>

        <!-- STEP 5: Publish -->
        <div x-show="step === 5" class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">{{ __('admin.publish') }}</h3>

            <div class="bg-gray-50 p-6 rounded border border-gray-200 text-center">
                <label class="flex items-center justify-center space-x-4 cursor-pointer mb-6">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $project->is_active ?? true) ? 'checked' : '' }} class="form-checkbox h-6 w-6 text-green-600">
                    <span class="mr-3 text-lg font-bold text-gray-700">{{ __('admin.activate_project') }}</span>
                </label>

                <p class="text-gray-500 mb-6">{{ __('admin.save_note') }}</p>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-12 rounded-lg text-lg shadow-lg transform transition hover:scale-105">
                    {{ $isEdit ? __('admin.save_changes') : __('admin.save_project') }}
                </button>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
            <button type="button" x-show="step > 1" @click="step--" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
                {{ __('admin.previous') }}
            </button>
            <div class="flex-1"></div>
            <button type="button" x-show="step < 5" @click="nextStep()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                {{ __('admin.next') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function projectForm(isEdit, projectId) {
        return {
            step: 1,
            searchQuery: '',
            searchResults: [],
            showCascading: isEdit, // Show if editing

            // Dropdowns
            selectedCountry: '{{ old('country_id', $project->country_id ?? '') }}',
            selectedRegion: '{{ old('region_id', $project->region_id ?? '') }}',
            selectedCity: '{{ old('city_id', $project->city_id ?? '') }}',
            selectedDistrict: '{{ old('district_id', $project->district_id ?? '') }}',

            regions: [],
            cities: [],
            districts: [],

            init() {
                // Pre-load dropdowns if values exist (Edit mode)
                if (this.selectedCountry) this.fetchRegions();
                if (this.selectedRegion) this.fetchCities();
                if (this.selectedCity) this.fetchDistricts();
            },

            goToStep(n) {
                // Validation logic can go here
                this.step = n;
            },

            nextStep() {
                if (this.step === 1 && !this.validateStep1()) return;
                this.step++;
            },

            validateStep1() {
                if (!this.selectedCountry || !this.selectedRegion || !this.selectedCity) {
                    alert('{{ __('admin.fill_location') }}');
                    return false;
                }
                return true;
            },

            async performSearch() {
                if (this.searchQuery.length < 2) return;
                let res = await fetch(`/admin/locations/search?q=${this.searchQuery}`);
                this.searchResults = await res.json();
            },

            selectSearchResult(result) {
                this.searchQuery = result.name;
                this.searchResults = [];
                this.showCascading = true;

                // Set Dropdown Values from Result Data
                // Note: result.data contains IDs. We need to set them and trigger fetches sequentially or rely on simple binding?
                // Binding won't populate options automatically. We must fetch.

                this.selectedCountry = result.data.country_id;
                this.fetchRegions().then(() => {
                    this.selectedRegion = result.data.region_id;
                    this.fetchCities().then(() => {
                        this.selectedCity = result.data.city_id;
                        if(result.data.city_id) {
                            this.fetchDistricts().then(() => {
                                this.selectedDistrict = result.data.district_id;
                            });
                        }
                    });
                });

                // Center Map
                if (result.lat && result.lng) {
                     // Leaflet map instance
                     if(window.mapInstance) {
                         window.mapInstance.flyTo([result.lat, result.lng], 13);
                         // Update hidden inputs if no polygon
                         document.getElementById('lat').value = result.lat;
                         document.getElementById('lng').value = result.lng;
                     }
                }
            },

            async fetchRegions() {
                if(!this.selectedCountry) return;
                let res = await fetch(`/admin/locations/countries/${this.selectedCountry}`);
                let data = await res.json();
                this.regions = data.regions;
                // Reset child
                // this.selectedRegion = ''; // Keep if valid?
            },
            async fetchCities() {
                if(!this.selectedRegion) return;
                let res = await fetch(`/admin/locations/regions/${this.selectedRegion}`);
                let data = await res.json();
                this.cities = data.cities;
            },
            async fetchDistricts() {
                if(!this.selectedCity) return;
                let res = await fetch(`/admin/locations/cities/${this.selectedCity}`);
                let data = await res.json();
                this.districts = data.districts;
            }
        }
    }

    function initMap() {
        var lat = parseFloat(document.getElementById('lat').value) || 30.0444;
        var lng = parseFloat(document.getElementById('lng').value) || 31.2357;

        var map = L.map('project_map').setView([lat, lng], 10);
        window.mapInstance = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Load existing polygon
        var existingPoly = document.getElementById('map_polygon').value;
        if (existingPoly && existingPoly !== 'null') {
            try {
                var geoJson = JSON.parse(existingPoly);
                // If it's pure geometry (type: Polygon), wrap in Feature or L.geoJSON handles geometry too?
                // L.geoJSON expects GeoJSON object (Feature, FeatureCollection, or Geometry).
                var layer = L.geoJSON(geoJson).addTo(drawnItems);
                map.fitBounds(layer.getBounds());
            } catch(e) { console.error("Map JSON Error", e); }
        } else if (lat && lng && lat !== 30.0444) {
             L.marker([lat, lng]).addTo(map);
        }

        var drawControl = new L.Control.Draw({
            draw: {
                polygon: true,
                marker: false,
                circle: false,
                rectangle: true,
                polyline: false,
                circlemarker: false
            },
            edit: {
                featureGroup: drawnItems,
                remove: true
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            var layer = e.layer;
            drawnItems.addLayer(layer);
            updatePolygonInput(layer);
        });

        map.on(L.Draw.Event.EDITED, function (e) {
            e.layers.eachLayer(function(layer) {
                updatePolygonInput(layer);
            });
        });

        map.on(L.Draw.Event.DELETED, function (e) {
            document.getElementById('map_polygon').value = '';
        });

        function updatePolygonInput(layer) {
            var geoJson = layer.toGeoJSON();
            document.getElementById('map_polygon').value = JSON.stringify(geoJson.geometry);

            // Update Center Lat/Lng
            var center = layer.getBounds().getCenter();
            document.getElementById('lat').value = center.lat;
            document.getElementById('lng').value = center.lng;
        }
    }
</script>
@endpush
