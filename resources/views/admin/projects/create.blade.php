@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' ' . __('admin.projects'))

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.projects.store') }}">
            @csrf

            <!-- Basic Info -->
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name_en" class="block text-sm font-medium text-gray-700">@lang('admin.name_en') *</label>
                    <input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="name_ar" class="block text-sm font-medium text-gray-700">@lang('admin.name_ar')</label>
                    <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

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
                    <label for="status" class="block text-sm font-medium text-gray-700">@lang('admin.status') *</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="planned" {{ old('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="under_construction" {{ old('status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                        <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>

                <div>
                    <label for="delivery_year" class="block text-sm font-medium text-gray-700">Delivery Year</label>
                    <input type="number" name="delivery_year" id="delivery_year" value="{{ old('delivery_year') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" min="2000" max="2100">
                </div>
            </div>

            <!-- Location -->
            <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                <div>
                    <label for="lat" class="block text-sm font-medium text-gray-700">@lang('admin.lat')</label>
                    <input type="text" name="lat" id="lat" value="{{ old('lat') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="lng" class="block text-sm font-medium text-gray-700">@lang('admin.lng')</label>
                    <input type="text" name="lng" id="lng" value="{{ old('lng') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
            </div>

            <!-- Details -->
            <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="tagline_en" class="block text-sm font-medium text-gray-700">Tagline (EN)</label>
                    <input type="text" name="tagline_en" id="tagline_en" value="{{ old('tagline_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="tagline_ar" class="block text-sm font-medium text-gray-700">Tagline (AR)</label>
                    <input type="text" name="tagline_ar" id="tagline_ar" value="{{ old('tagline_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="md:col-span-2">
                    <label for="description_en" class="block text-sm font-medium text-gray-700">Description (EN)</label>
                    <textarea name="description_en" id="description_en" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description_en') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="description_ar" class="block text-sm font-medium text-gray-700">Description (AR)</label>
                    <textarea name="description_ar" id="description_ar" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description_ar') }}</textarea>
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
            </div>

             <!-- SEO -->
             <h3 class="text-lg font-medium text-gray-900 mb-4">SEO</h3>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                 <div>
                     <label for="seo_slug_en" class="block text-sm font-medium text-gray-700">SEO Slug (EN)</label>
                     <input type="text" name="seo_slug_en" id="seo_slug_en" value="{{ old('seo_slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                 </div>
                 <div>
                     <label for="seo_slug_ar" class="block text-sm font-medium text-gray-700">SEO Slug (AR)</label>
                     <input type="text" name="seo_slug_ar" id="seo_slug_ar" value="{{ old('seo_slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                 </div>
                 <div>
                     <label for="meta_title_en" class="block text-sm font-medium text-gray-700">Meta Title (EN)</label>
                     <input type="text" name="meta_title_en" id="meta_title_en" value="{{ old('meta_title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                 </div>
                 <div>
                     <label for="meta_title_ar" class="block text-sm font-medium text-gray-700">Meta Title (AR)</label>
                     <input type="text" name="meta_title_ar" id="meta_title_ar" value="{{ old('meta_title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                 </div>
             </div>

            <div class="flex items-center mb-6">
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

<script>
    function fetchRegions(countryId) {
        if (!countryId) return;
        fetch(`/admin/locations/countries/${countryId}`)
            .then(response => response.json())
            .then(data => {
                const regionSelect = document.getElementById('region_id');
                regionSelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';
                data.regions.forEach(region => {
                    regionSelect.innerHTML += `<option value="${region.id}">${region.name_en}</option>`;
                });
            });
    }

    function fetchCities(regionId) {
        if (!regionId) return;
        fetch(`/admin/locations/regions/${regionId}`)
            .then(response => response.json())
            .then(data => {
                const citySelect = document.getElementById('city_id');
                citySelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';
                data.cities.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.id}">${city.name_en}</option>`;
                });
            });
    }

    function fetchDistricts(cityId) {
        if (!cityId) return;
        fetch(`/admin/locations/cities/${cityId}`)
            .then(response => response.json())
            .then(data => {
                const districtSelect = document.getElementById('district_id');
                districtSelect.innerHTML = '<option value="">@lang("admin.select_option")</option>';
                data.districts.forEach(district => {
                    districtSelect.innerHTML += `<option value="${district.id}">${district.name_en}</option>`;
                });
            });
    }
</script>
@endsection
