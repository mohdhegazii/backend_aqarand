@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' ' . __('admin.cities'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'cities.update', $city) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.region')</label>
                    <select name="region_id" id="region_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- @lang('admin.region') --</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ old('region_id', $city->region_id) == $region->id ? 'selected' : '' }}>
                                {{ $region->getName() }} ({{ $region->country->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $city->name_en) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                    <input type="text" name="name_local" value="{{ old('name_local', $city->name_local) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $city->is_active) ? 'checked' : '' }}>
                        <span class="mx-2">@lang('admin.activate')</span>
                    </label>
                </div>

                @include('admin.partials.map_picker', [
                    'lat' => old('lat', $city->lat),
                    'lng' => old('lng', $city->lng),
                    'boundary' => old('boundary_geojson', $city->boundary_geojson),
                    'mapId' => 'city-map'
                ])

                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse mt-4">
                    <a href="{{ route($adminRoutePrefix.'cities.index') }}" class="text-gray-600 hover:text-gray-900">
                        @lang('admin.cancel')
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        @lang('admin.save')
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('region_id').addEventListener('change', function() {
            var regionId = this.value;
            if (regionId) {
                fetch('/admin/locations/cities/' + regionId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.lat && data.lng) {
                            if (window['map_city-map']) {
                                window['map_city-map'].flyTo([data.lat, data.lng], 11);
                            }
                        }
                    });
            }
        });
    </script>
@endsection
