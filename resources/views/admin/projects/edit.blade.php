@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        .step-active { border-bottom: 2px solid #2563eb; color: #2563eb; }
        .step-inactive { border-bottom: 2px solid transparent; color: #6b7280; }
    </style>
@endpush

@section('header')
    تعديل المشروع: {{ $project->name_ar ?? $project->name }}
@endsection

@section('content')
<div x-data="projectForm()" x-init="initForm()" class="bg-white rounded-lg shadow-md p-6">

    <!-- Steps Navigation -->
    <div class="flex border-b border-gray-200 mb-6">
        <button type="button" @click="step = 1" :class="step === 1 ? 'step-active' : 'step-inactive'" class="py-2 px-4 font-semibold text-lg focus:outline-none">
            الخطوة ١: بيانات المشروع
        </button>
        <button type="button" @click="validateStep1() ? step = 2 : null" :class="step === 2 ? 'step-active' : 'step-inactive'" class="py-2 px-4 font-semibold text-lg focus:outline-none">
            الخطوة ٢: الوسائط (الصور والبروشور)
        </button>
    </div>

    <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- STEP 1: Basic Info & Location -->
        <div x-show="step === 1" class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name AR -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">اسم المشروع (عربي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name_ar" required class="w-full rounded border-gray-300 p-2" value="{{ old('name_ar', $project->name_ar) }}">
                </div>
                <!-- Name EN -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">اسم المشروع (إنجليزي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name_en" required class="w-full rounded border-gray-300 p-2" value="{{ old('name_en', $project->name_en) }}">
                </div>

                <!-- Developer -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">المطور العقاري</label>
                    <select name="developer_id" class="w-full rounded border-gray-300 p-2">
                        <option value="">اختر المطور</option>
                        @foreach($developers as $developer)
                            <option value="{{ $developer->id }}" {{ old('developer_id', $project->developer_id) == $developer->id ? 'selected' : '' }}>{{ $developer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tagline -->
                <div>
                    <label class="block text-gray-700 font-bold mb-2">شعار نصي (Tagline)</label>
                    <input type="text" name="tagline_ar" class="w-full rounded border-gray-300 p-2" value="{{ old('tagline_ar', $project->tagline_ar) }}">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">وصف المشروع</label>
                <textarea name="description_long" rows="4" class="w-full rounded border-gray-300 p-2">{{ old('description_long', $project->description_long) }}</textarea>
            </div>

            <hr class="border-gray-200">

            <!-- LOCATION SECTION -->
            <div>
                <h3 class="text-lg font-bold mb-4">موقع المشروع</h3>

                <!-- Unified Search -->
                <div class="mb-4 relative">
                    <label class="block text-gray-700 font-bold mb-2">بحث موحد</label>
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch" placeholder="ابحث لتغيير الموقع..." class="w-full rounded border-gray-300 p-2">
                    <div x-show="searchResults.length > 0" class="absolute z-50 bg-white border border-gray-200 w-full mt-1 rounded shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="result in searchResults" :key="result.id">
                            <div @click="selectLocation(result)" class="p-2 hover:bg-gray-100 cursor-pointer border-b">
                                <span x-text="result.name"></span> <span class="text-xs text-gray-500" x-text="'(' + result.type + ')'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Cascading Dropdowns -->
                <div x-show="locationSelected" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">الدولة <span class="text-red-500">*</span></label>
                        <select name="country_id" x-model="selectedCountry" @change="fetchRegions" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر الدولة</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name_local ?? $country->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">المحافظة <span class="text-red-500">*</span></label>
                        <select name="region_id" x-model="selectedRegion" @change="fetchCities" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر المحافظة</option>
                            <template x-for="region in regions" :key="region.id">
                                <option :value="region.id" x-text="region.name_local || region.name_en" :selected="region.id == selectedRegion"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">المدينة <span class="text-red-500">*</span></label>
                        <select name="city_id" x-model="selectedCity" @change="fetchDistricts" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر المدينة</option>
                            <template x-for="city in cities" :key="city.id">
                                <option :value="city.id" x-text="city.name_local || city.name_en" :selected="city.id == selectedCity"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">الحي</label>
                        <select name="district_id" x-model="selectedDistrict" class="w-full rounded border-gray-300 text-sm">
                            <option value="">اختر الحي</option>
                            <template x-for="district in districts" :key="district.id">
                                <option :value="district.id" x-text="district.name_local || district.name_en" :selected="district.id == selectedDistrict"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- MAP SECTION -->
            <div>
                <h3 class="text-lg font-bold mb-2">خريطة المشروع وحدود الأرض</h3>
                <div id="map" style="height: 400px;" class="w-full rounded border border-gray-300 z-0"></div>
                <input type="hidden" name="map_polygon" id="map_polygon_input" value="{{ is_string($project->map_polygon) ? $project->map_polygon : json_encode($project->map_polygon) }}">
                <input type="hidden" name="lat" id="lat_input" value="{{ $project->lat }}">
                <input type="hidden" name="lng" id="lng_input" value="{{ $project->lng }}">
            </div>

        </div>

        <!-- STEP 2: Media -->
        <div x-show="step === 2" class="space-y-6">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">قسم الوسائط</h3>

            <!-- Hero Image -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-2">تحديث صورة الغلاف (Hero Image)</label>
                <div class="flex items-center space-x-4 rtl:space-x-reverse mb-2">
                    @if($project->hero_image_url)
                        <img src="{{ asset('storage/'.$project->hero_image_url) }}" class="h-16 w-16 object-cover rounded border">
                        <span class="text-sm text-gray-500">الصورة الحالية</span>
                    @endif
                </div>
                <input type="file" name="hero_image" accept="image/*" class="w-full">
            </div>

            <!-- Existing Gallery Management -->
            <div class="bg-white p-4 rounded border border-gray-200 shadow-sm">
                <h4 class="font-bold mb-4">إدارة الصور الحالية</h4>
                @if(!empty($project->gallery) && is_array($project->gallery))
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($project->gallery as $key => $image)
                            @php
                                $path = is_array($image) ? $image['path'] : $image;
                                $name = is_array($image) ? ($image['name'] ?? '') : '';
                                $alt = is_array($image) ? ($image['alt'] ?? '') : '';
                                $isHero = $project->hero_image_url === $path;
                            @endphp
                            <div class="border rounded p-3 relative group bg-gray-50">
                                <img src="{{ asset('storage/' . $path) }}" class="w-full h-32 object-cover rounded mb-2">

                                <input type="hidden" name="gallery_data[{{$key}}][path]" value="{{ $path }}">

                                <div class="mb-2">
                                    <label class="text-xs font-bold text-gray-600">اسم الصورة</label>
                                    <input type="text" name="gallery_data[{{$key}}][name]" value="{{ $name }}" class="w-full text-xs rounded border-gray-300 p-1">
                                </div>
                                <div class="mb-2">
                                    <label class="text-xs font-bold text-gray-600">نص بديل (Alt)</label>
                                    <input type="text" name="gallery_data[{{$key}}][alt]" value="{{ $alt }}" class="w-full text-xs rounded border-gray-300 p-1">
                                </div>

                                <div class="flex items-center justify-between mt-2">
                                    <label class="inline-flex items-center text-xs cursor-pointer">
                                        <input type="radio" name="selected_hero" value="{{ $path }}" {{ $isHero ? 'checked' : '' }} class="form-radio text-blue-600 h-4 w-4">
                                        <span class="mr-2">تعيين كغلاف</span>
                                    </label>

                                    <button type="button" onclick="this.closest('.group').remove()" class="text-red-500 hover:text-red-700 p-1" title="حذف الصورة">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">لا توجد صور في المعرض حالياً.</p>
                @endif
            </div>

            <!-- Add New Gallery Images -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200 mt-4">
                <label class="block text-gray-700 font-bold mb-2">إضافة صور جديدة للمعرض</label>
                <input type="file" name="gallery[]" accept="image/*" multiple class="w-full">
            </div>

            <!-- Brochure -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                <label class="block text-gray-700 font-bold mb-2">البروشور (PDF)</label>
                @if($project->brochure_url)
                    <div class="flex items-center mb-2">
                        <i class="bi bi-file-pdf text-red-500 text-xl ml-2"></i>
                        <a href="{{ asset('storage/'.$project->brochure_url) }}" target="_blank" class="text-blue-600 hover:underline text-sm">عرض البروشور الحالي</a>
                    </div>
                @endif
                <input type="file" name="brochure" accept="application/pdf" class="w-full">
            </div>

        </div>

        <!-- Buttons -->
        <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
            <button type="button" x-show="step > 1" @click="step--" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
                السابق
            </button>
            <div class="flex-1"></div>
            <button type="button" x-show="step < 2" @click="validateStep1() && step++" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                التالي
            </button>
            <button type="submit" x-show="step === 2" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                حفظ التعديلات
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
            locationSelected: true, // Initially true for edit if data exists

            selectedCountry: '',
            selectedRegion: '',
            selectedCity: '',
            selectedDistrict: '',

            regions: [],
            cities: [],
            districts: [],

            async initForm() {
                // Pre-populate logic
                this.selectedCountry = "{{ $project->country_id }}";
                if(this.selectedCountry) await this.fetchRegions();

                this.selectedRegion = "{{ $project->region_id }}";
                if(this.selectedRegion) await this.fetchCities();

                this.selectedCity = "{{ $project->city_id }}";
                if(this.selectedCity) await this.fetchDistricts();

                this.selectedDistrict = "{{ $project->district_id }}";

                initMap();
            },

            validateStep1() {
                if(!this.selectedCountry || !this.selectedRegion || !this.selectedCity) {
                    alert('يرجى استكمال بيانات الموقع');
                    return false;
                }
                return true;
            },

            async performSearch() {
                if (this.searchQuery.length < 2) return;
                try {
                    let response = await fetch(`/admin/locations/search?q=${this.searchQuery}`);
                    this.searchResults = await response.json();
                } catch (e) { console.error(e); }
            },

            selectLocation(result) {
                this.searchQuery = result.name;
                this.searchResults = [];
                if (result.lat && result.lng) {
                     window.mapInstance.setView([result.lat, result.lng], 13);
                     document.getElementById('lat_input').value = result.lat;
                     document.getElementById('lng_input').value = result.lng;
                }
            },

            async fetchRegions() {
                this.regions = [];
                // Don't reset selectedRegion if we are initiating?
                // Logic: If user changes country, reset region. If script calls it, preserve.
                // We'll rely on bound value. If selectedRegion is not in new list, it becomes invalid visually.

                if(!this.selectedCountry) return;
                let res = await fetch(`/admin/locations/countries/${this.selectedCountry}`);
                this.regions = await res.json();
            },

            async fetchCities() {
                this.cities = [];
                if(!this.selectedRegion) return;
                let res = await fetch(`/admin/locations/regions/${this.selectedRegion}`);
                this.cities = await res.json();
            },

            async fetchDistricts() {
                this.districts = [];
                if(!this.selectedCity) return;
                let res = await fetch(`/admin/locations/cities/${this.selectedCity}`);
                this.districts = await res.json();
            }
        }
    }

    function initMap() {
        var lat = {{ $project->lat ?? 30.0444 }};
        var lng = {{ $project->lng ?? 31.2357 }};

        var map = L.map('map').setView([lat, lng], 13);
        window.mapInstance = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Load existing polygon
        var existingPolygon = @json($project->map_polygon);
        if (existingPolygon) {
             // If stored as {type:..., coordinates:...} GeoJSON geometry
             var geoJsonLayer = L.geoJSON(existingPolygon);
             geoJsonLayer.eachLayer(function(l) {
                 drawnItems.addLayer(l);
             });
             try {
                 map.fitBounds(drawnItems.getBounds());
             } catch(e) {}
        }

        var drawControl = new L.Control.Draw({
            draw: {
                polygon: true,
                marker: false,
                circle: false,
                circlemarker: false,
                rectangle: true,
                polyline: false
            },
            edit: {
                featureGroup: drawnItems,
                remove: true
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            drawnItems.addLayer(e.layer);
            updateInputs(e.layer);
        });

        map.on(L.Draw.Event.EDITED, function (e) {
            e.layers.eachLayer(function (layer) {
                updateInputs(layer);
            });
        });

        map.on(L.Draw.Event.DELETED, function(e) {
             document.getElementById('map_polygon_input').value = '';
        });

        function updateInputs(layer) {
            var geoJson = layer.toGeoJSON();
            document.getElementById('map_polygon_input').value = JSON.stringify(geoJson.geometry);

            var center = layer.getBounds().getCenter();
            document.getElementById('lat_input').value = center.lat;
            document.getElementById('lng_input').value = center.lng;
        }
    }
</script>
@endpush
