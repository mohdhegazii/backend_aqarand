@extends('admin.layouts.app')

@section('header', __('admin.create') . ' ' . __('admin.developers'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('admin.developers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_en')</label>
                    <textarea name="description_en" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_en') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_ar')</label>
                    <textarea name="description_ar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_ar') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.logo')</label>
                    <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.website_url')</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span class="mx-2">@lang('admin.activate')</span>
                    </label>
                </div>

                @include('admin.partials.seo_meta_box', ['model' => new \App\Models\Developer()])

                <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse mt-4">
                    <a href="{{ route('admin.developers.index') }}" class="text-gray-600 hover:text-gray-900">
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
