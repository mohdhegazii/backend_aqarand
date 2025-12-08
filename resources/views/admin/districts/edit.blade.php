@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' ' . __('admin.districts'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'districts.update', $district) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.city')</label>
                    <select name="city_id" id="city_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- @lang('admin.city') --</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id', $district->city_id) == $city->id ? 'selected' : '' }}>
                                {{ $city->getName() }} ({{ $city->region->getName() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $district->name_en) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                    <input type="text" name="name_local" value="{{ old('name_local', $district->name_local) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $district->is_active) ? 'checked' : '' }}>
                        <span class="mx-2">@lang('admin.activate')</span>
                    </label>
                </div>

                @include('admin.partials.map_picker', [
                    'lat' => old('lat', $district->lat),
                    'lng' => old('lng', $district->lng),
                    'boundary' => old('boundary_geojson', $district->boundary_geojson),
                    'mapId' => 'district-map'
                ])

                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse mt-4">
                    <a href="{{ route($adminRoutePrefix.'districts.index') }}" class="text-gray-600 hover:text-gray-900">
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
        document.getElementById('city_id').addEventListener('change', function() {
            var cityId = this.value;
            if (cityId) {
                fetch('/admin/locations/districts/' + cityId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.lat && data.lng) {
                            if (window['map_district-map']) {
                                window['map_district-map'].flyTo([data.lat, data.lng], 13);
                            }
                        }
                    });
            }
        });
    </script>
@endsection
