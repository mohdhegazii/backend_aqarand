@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        .step-active { border-bottom: 2px solid #2563eb; color: #2563eb; }
        .step-inactive { border-bottom: 2px solid transparent; color: #6b7280; }
    </style>
@endpush

@section('header')
    إضافة مشروع جديد
@endsection

@section('content')
<div x-data="projectForm()" x-init="initMap()" class="bg-white rounded-lg shadow-md p-6">

    <!-- Steps Navigation -->
    <div class="flex border-b border-gray-200 mb-6">
        <button type="button" @click="step = 1" :class="step === 1 ? 'step-active' : 'step-inactive'" class="py-2 px-4 font-semibold text-lg focus:outline-none">
            الخطوة ١: بيانات المشروع
        </button>
        <button type="button" @click="validateStep1() ? step = 2 : null" :class="step === 2 ? 'step-active' : 'step-inactive'" class="py-2 px-4 font-semibold text-lg focus:outline-none">
            الخطوة ٢: الوسائط (الصور والبروشور)
        </button>
    </div>

    <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- STEP 1: Basic Info & Location -->
        <div x-show="step === 1" class="space-y-6">

            <!-- Basic Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name AR -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">اسم المشروع (عربي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name_ar" required class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('name_ar') }}">
                </div>
                <!-- Name EN -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">اسم المشروع (إنجليزي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name_en" required class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('name_en') }}">
                </div>

                <!-- Developer -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">المطور العقاري</label>
                    <select name="developer_id" class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر المطور</option>
                        @foreach($developers as $developer)
                            <option value="{{ $developer->id }}" {{ old('developer_id') == $developer->id ? 'selected' : '' }}>{{ $developer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tagline -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">شعار نصي (Tagline)</label>
                    <input type="text" name="tagline_ar" class="w-full rounded border-gray-300 p-2" value="{{ old('tagline_ar') }}" placeholder="مثال: واجهة سكنية فاخرة">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">وصف المشروع</label>
                <textarea name="description_long" rows="4" class="w-full rounded border-gray-300 p-2">{{ old('description_long') }}</textarea>
            </div>

            <hr class="border-gray-200">

            <!-- LOCATION SECTION -->
            <div>
                <h3 class="text-lg font-bold mb-4">موقع المشروع</h3>

                <!-- Unified Search -->
                <div class="mb-4 relative">
                    <label class="block text-gray-700 font-bold mb-2">بحث موحد</label>
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch" placeholder="ابحث عن موقع المشروع (مدينة، حي، مشروع...)" class="w-full rounded border-gray-300 p-2">

                    <!-- Search Results -->
                    <div x-show="searchResults.length > 0" class="absolute z-50 bg-white border border-gray-200 w-full mt-1 rounded shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="result in searchResults" :key="result.id">
                            <div @click="selectLocation(result)" class="p-2 hover:bg-gray-100 cursor-pointer border-b">
                                <span x-text="result.name"></span> <span class="text-xs text-gray-500" x-text="'(' + result.type + ')'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Cascading Dropdowns -->
                <div x-show="locationSelected || searchQuery.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded">
                    <!-- Country -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">الدولة <span class="text-red-500">*</span></label>
                        <select name="country_id" x-model="selectedCountry" @change="fetchRegions" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر الدولة</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name_local ?? $country->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Region -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">المحافظة <span class="text-red-500">*</span></label>
                        <select name="region_id" x-model="selectedRegion" @change="fetchCities" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر المحافظة</option>
                            <template x-for="region in regions" :key="region.id">
                                <option :value="region.id" x-text="region.name_local || region.name_en"></option>
                            </template>
                        </select>
                    </div>
                    <!-- City -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">المدينة <span class="text-red-500">*</span></label>
                        <select name="city_id" x-model="selectedCity" @change="fetchDistricts" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر المدينة</option>
                            <template x-for="city in cities" :key="city.id">
                                <option :value="city.id" x-text="city.name_local || city.name_en"></option>
                            </template>
                        </select>
                    </div>
                    <!-- District -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">الحي</label>
                        <select name="district_id" x-model="selectedDistrict" class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر الحي</option>
                            <template x-for="district in districts" :key="district.id">
                                <option :value="district.id" x-text="district.name_local || district.name_en"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- MAP SECTION -->
            <div>
                <h3 class="text-lg font-bold mb-2">خريطة المشروع وحدود الأرض</h3>
                <div id="map" style="height: 400px;" class="w-full rounded border border-gray-300 z-0"></div>
                <input type="hidden" name="map_polygon" id="map_polygon_input">
                <input type="hidden" name="lat" id="lat_input">
                <input type="hidden" name="lng" id="lng_input">
            </div>

        </div>

        <!-- STEP 2: Media -->
        <div x-show="step === 2" class="space-y-6">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">قسم الوسائط</h3>

            <!-- Hero Image -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-2">صورة الغلاف (Hero Image) <span class="text-red-500">*</span></label>
                <input type="file" name="hero_image" accept="image/*" required class="w-full">
                <p class="text-xs text-gray-500 mt-1">يجب رفع صورة للغلاف. سيتم ضغطها وتحويلها إلى WebP.</p>
            </div>

            <!-- Gallery -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-2">صور المعرض (Gallery)</label>
                <input type="file" name="gallery[]" accept="image/*" multiple class="w-full">
                <p class="text-xs text-gray-500 mt-1">يمكنك اختيار صور متعددة. سيتم معالجتها جميعاً.</p>
            </div>

            <!-- Brochure -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-2">البروشور (PDF)</label>
                <input type="file" name="brochure" accept="application/pdf" class="w-full">
                <p class="text-xs text-gray-500 mt-1">ملف PDF فقط. الحد الأقصى ٥ ميجابايت.</p>
            </div>

        </div>

        <!-- Buttons -->
        <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
            <button type="button" x-show="step > 1" @click="step--" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
                السابق
            </button>
            <div class="flex-1"></div> <!-- Spacer -->
            <button type="button" x-show="step < 2" @click="validateStep1() && step++" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                التالي
            </button>
            <button type="submit" x-show="step === 2" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                حفظ المشروع
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
    function projectForm() {
        return {
            step: 1,
            searchQuery: '',
            searchResults: [],
            locationSelected: false,

            // Dropdowns data
            selectedCountry: '',
            selectedRegion: '',
            selectedCity: '',
            selectedDistrict: '',

            regions: [],
            cities: [],
            districts: [],

            validateStep1() {
                if(!this.selectedCountry || !this.selectedRegion || !this.selectedCity) {
                    alert('يرجى استكمال بيانات الموقع (الدولة، المحافظة، المدينة)');
                    return false;
                }
                return true;
            },

            async performSearch() {
                if (this.searchQuery.length < 2) return;
                try {
                    // Using the existing search route
                    let response = await fetch(`/admin/locations/search?q=${this.searchQuery}`);
                    this.searchResults = await response.json();
                } catch (e) {
                    console.error("Search error", e);
                }
            },

            selectLocation(result) {
                // Assuming result has structure that helps us populate fields.
                // The search API might return specific format.
                // If it's a simple search, we might just center map.
                // But cascading dropdowns need IDs.
                // If result contains hierarchy IDs, great.
                // Otherwise we just center map and let user choose dropdowns.

                this.searchQuery = result.name;
                this.searchResults = [];
                this.locationSelected = true;

                // If the result has IDs, populate them (requires search API to return them)
                // For now, we center map if coordinates exist.
                if (result.lat && result.lng) {
                     window.mapInstance.setView([result.lat, result.lng], 13);
                     // Update hidden inputs
                     document.getElementById('lat_input').value = result.lat;
                     document.getElementById('lng_input').value = result.lng;
                }
            },

            async fetchRegions() {
                this.regions = []; this.selectedRegion = '';
                this.cities = []; this.selectedCity = '';
                this.districts = []; this.selectedDistrict = '';

                if(!this.selectedCountry) return;

                let res = await fetch(`/admin/admin/locations/countries/${this.selectedCountry}`); // Check prefix, double admin?
                // Route definition: 'prefix' => 'admin'. Route: 'locations/countries/{id}'.
                // So /admin/locations/countries/{id}
                res = await fetch(`/admin/locations/countries/${this.selectedCountry}`);
                this.regions = await res.json();
            },

            async fetchCities() {
                this.cities = []; this.selectedCity = '';
                this.districts = []; this.selectedDistrict = '';

                if(!this.selectedRegion) return;
                let res = await fetch(`/admin/locations/regions/${this.selectedRegion}`);
                this.cities = await res.json();
            },

            async fetchDistricts() {
                this.districts = []; this.selectedDistrict = '';

                if(!this.selectedCity) return;
                let res = await fetch(`/admin/locations/cities/${this.selectedCity}`);
                this.districts = await res.json();
            }
        }
    }

    function initMap() {
        // Default center (Cairo or user location)
        var map = L.map('map').setView([30.0444, 31.2357], 10);
        window.mapInstance = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // FeatureGroup is to store editable layers
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
                polygon: true,
                marker: false,
                circle: false,
                circlemarker: false,
                rectangle: true, // Rectangle is essentially a polygon
                polyline: false
            },
            edit: {
                featureGroup: drawnItems,
                remove: true
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            var layer = e.layer;
            drawnItems.clearLayers(); // Only allow one polygon
            drawnItems.addLayer(layer);

            updateInputs(layer);
            centerOnLayer(layer);
        });

        map.on(L.Draw.Event.EDITED, function (e) {
            var layers = e.layers;
            layers.eachLayer(function (layer) {
                updateInputs(layer);
                centerOnLayer(layer);
            });
        });

        map.on(L.Draw.Event.DELETED, function(e) {
             document.getElementById('map_polygon_input').value = '';
        });

        function updateInputs(layer) {
            var geoJson = layer.toGeoJSON();
            document.getElementById('map_polygon_input').value = JSON.stringify(geoJson.geometry);
        }

        function centerOnLayer(layer) {
            var bounds = layer.getBounds();
            var center = bounds.getCenter();

            // Add a temporary marker or just view
            // L.marker(center).addTo(map);

            map.setView(center, 14); // "FlyTo" behavior basically

            document.getElementById('lat_input').value = center.lat;
            document.getElementById('lng_input').value = center.lng;
        }
    }
</script>
@endpush
