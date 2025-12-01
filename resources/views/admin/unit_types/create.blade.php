@extends('admin.layouts.app')

@section('header', __('admin.create') . ' ' . __('admin.unit_types'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('admin.unit-types.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.property_type')</label>
                    <select name="property_type_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- @lang('admin.property_type') --</option>
                        @foreach($propertyTypes as $type)
                            <option value="{{ $type->id }}" {{ old('property_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name')</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.code')</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description')</label>
                    <textarea name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['requires_land_area', 'requires_built_up_area', 'requires_garden_area', 'requires_roof_area', 'requires_indoor_area', 'requires_outdoor_area'] as $field)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="{{ $field }}" value="1" class="form-checkbox" {{ old($field) ? 'checked' : '' }}>
                            <span class="mx-2">@lang('admin.' . $field)</span>
                        </label>
                    @endforeach
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span class="mx-2">@lang('admin.active')</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse">
                    <a href="{{ route('admin.unit-types.index') }}" class="text-gray-600 hover:text-gray-900">
                        @lang('admin.cancel')
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        @lang('admin.save')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
