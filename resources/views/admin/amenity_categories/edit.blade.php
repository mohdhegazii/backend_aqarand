@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' ' . __('admin.amenity_category'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'amenity-categories.update', $amenityCategory->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $amenityCategory->name_en) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                     @error('name_en')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $amenityCategory->name_ar) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                     @error('name_ar')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                 <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.sort_order')</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $amenityCategory->sort_order) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $amenityCategory->is_active) ? 'checked' : '' }}>
                        <span class="mx-2">@lang('admin.active')</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse">
                    <a href="{{ route($adminRoutePrefix.'amenity-categories.index') }}" class="text-gray-600 hover:text-gray-900">
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
