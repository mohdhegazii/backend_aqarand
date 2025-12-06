@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' ' . __('admin.categories'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Name EN -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name_en">@lang('admin.name_en')</label>
                    <input type="text" name="name_en" id="name_en" value="{{ old('name_en', $category->name_en) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name_en') border-red-500 @enderror" required>
                    @error('name_en')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name AR -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name_ar">@lang('admin.name_ar')</label>
                    <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar', $category->name_ar) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name_ar') border-red-500 @enderror" required>
                    @error('name_ar')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">@lang('admin.image')</label>
                    @if ($category->image_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $category->image_path) }}" alt="Category Image" class="h-20 w-auto rounded border">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('image') border-red-500 @enderror">
                    @error('image')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" class="form-checkbox text-blue-500" value="1" {{ $category->is_active ? 'checked' : '' }}>
                        <span class="ml-2">@lang('admin.is_active')</span>
                    </label>
                    @error('is_active')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse mt-4">
                    <a href="{{ route($adminRoutePrefix.'categories.index') }}" class="text-gray-600 hover:text-gray-900">
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
