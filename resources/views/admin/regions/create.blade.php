@extends('admin.layouts.app')

@section('header', __('admin.create') . ' ' . __('admin.regions'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'regions.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.country')</label>
                    <select name="country_id" id="country_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- @lang('admin.countries') --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->getName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                    <input type="text" name="name_local" value="{{ old('name_local') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span class="mx-2">@lang('admin.activate')</span>
                    </label>
                </div>

                <x-location.map
                    :lat="old('lat')"
                    :lng="old('lng')"
                    mapId="region-map"
                    entityLevel="region"
                />

                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse mt-4">
                    <a href="{{ route($adminRoutePrefix.'regions.index') }}" class="text-gray-600 hover:text-gray-900">
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
        document.getElementById('country_id').addEventListener('change', function() {
            var countryId = this.value;
            if (countryId) {
                fetch('/admin/locations/regions/' + countryId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.lat && data.lng) {
                            if (window['map_region-map']) {
                                window['map_region-map'].flyTo([data.lat, data.lng], 10);
                            }
                        }
                    });
            }
        });
    </script>
@endsection
