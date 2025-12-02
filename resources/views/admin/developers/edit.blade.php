@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' ' . __('admin.developers'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('admin.developers.update', $developer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Tabs Navigation -->
                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 border-blue-600 text-blue-600" id="en-tab" data-tabs-target="#en" type="button" role="tab" aria-controls="en" aria-selected="true">
                                English
                            </button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="ar-tab" data-tabs-target="#ar" type="button" role="tab" aria-controls="ar" aria-selected="false">
                                العربية
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tabs Content -->
                <div id="myTabContent">
                    <!-- English Tab -->
                    <div class="p-4 rounded-lg bg-gray-50" id="en" role="tabpanel" aria-labelledby="en-tab">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                            <input type="text" name="name_en" value="{{ old('name_en', $developer->name_en) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_en')</label>
                            <textarea name="description_en" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_en', $developer->description_en) }}</textarea>
                        </div>
                        @include('admin.partials.seo_meta_box', ['model' => $developer, 'locale' => 'en'])
                    </div>

                    <!-- Arabic Tab -->
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="ar" role="tabpanel" aria-labelledby="ar-tab">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar', $developer->name_ar) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right" dir="rtl">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_ar')</label>
                            <textarea name="description_ar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm text-right" rows="6" dir="rtl">{{ old('description_ar', $developer->description_ar) }}</textarea>
                        </div>
                        @include('admin.partials.seo_meta_box', ['model' => $developer, 'locale' => 'ar'])
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="mt-6 border-t pt-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.logo')</label>
                        @if($developer->logo_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $developer->logo_path) }}" alt="Logo" class="h-16 object-contain">
                            </div>
                        @endif
                        <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.website_url')</label>
                        <input type="url" name="website_url" value="{{ old('website_url', $developer->website_url) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $developer->is_active) ? 'checked' : '' }}>
                            <span class="mx-2">@lang('admin.activate')</span>
                        </label>
                    </div>
                </div>

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

    <!-- Simple Tab Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('[role="tab"]');
            const panels = document.querySelectorAll('[role="tabpanel"]');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Reset
                    tabs.forEach(t => {
                        t.setAttribute('aria-selected', 'false');
                        t.classList.remove('text-blue-600', 'border-blue-600');
                        t.classList.add('border-transparent');
                    });
                    panels.forEach(p => p.classList.add('hidden'));

                    // Activate
                    tab.setAttribute('aria-selected', 'true');
                    tab.classList.remove('border-transparent');
                    tab.classList.add('text-blue-600', 'border-blue-600');

                    const target = document.querySelector(tab.getAttribute('data-tabs-target'));
                    target.classList.remove('hidden');
                });
            });
        });
    </script>
@endsection
