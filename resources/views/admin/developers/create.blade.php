@extends('admin.layouts.app')

@section('header', __('admin.create') . ' ' . __('admin.developers'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'developers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @php
                    $previewName = old('name_en') ?? old('name_ar') ?? __('admin.developers');
                    $previewAltEn = old('name_en');
                    $previewAltAr = old('name_ar');
                @endphp

                <div class="mb-6 p-4 bg-gray-50 border rounded flex items-center gap-4">
                    <div class="h-24 w-24 rounded border bg-white flex items-center justify-center overflow-hidden">
                        <img id="logo-preview" src="" alt="{{ $previewName }}" class="hidden h-full w-full object-contain">
                        <span id="logo-placeholder" class="text-[11px] text-gray-400 text-center px-2">@lang('admin.logo')</span>
                    </div>
                    <div class="flex-1 space-y-1">
                        <h3 id="preview-name" class="text-lg font-semibold text-gray-800">{{ $previewName }}</h3>
                        <div id="preview-name-en" class="text-sm text-gray-600">{{ $previewAltEn ?? __('admin.name_en') }}</div>
                        <div id="preview-name-ar" class="text-sm text-gray-600">{{ $previewAltAr ?? __('admin.name_ar') }}</div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 rounded-t-lg border-b-2 text-blue-600 hover:text-blue-600 border-blue-600" id="en-tab" data-tabs-target="#en" type="button" role="tab" aria-controls="en" aria-selected="true">English</button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" id="ar-tab" data-tabs-target="#ar" type="button" role="tab" aria-controls="ar" aria-selected="false">العربية</button>
                        </li>
                    </ul>
                </div>

                <!-- Tabs Content -->
                <div id="myTabContent">
                    <!-- English Tab -->
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="en" role="tabpanel" aria-labelledby="en-tab">
                         <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_en')</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_en')</label>
                            <textarea name="description_en" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_en') }}</textarea>
                        </div>

                        @include('admin.partials.seo_meta_box', ['locale' => 'en', 'seoMeta' => new \App\Models\SeoMeta()])
                    </div>

                    <!-- Arabic Tab -->
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="ar" role="tabpanel" aria-labelledby="ar-tab">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_ar')</label>
                            <textarea name="description_ar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_ar') }}</textarea>
                        </div>

                        @include('admin.partials.seo_meta_box', ['locale' => 'ar', 'seoMeta' => new \App\Models\SeoMeta()])
                    </div>
                </div>

                <div class="mt-6 border-t pt-6">
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

                    <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse">
                        <a href="{{ route($adminRoutePrefix.'developers.index') }}" class="text-gray-600 hover:text-gray-900">
                            @lang('admin.cancel')
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            @lang('admin.save')
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Simple Tab Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = [
                { id: 'en-tab', target: 'en' },
                { id: 'ar-tab', target: 'ar' }
            ];

            tabs.forEach(tab => {
                const button = document.getElementById(tab.id);
                button.addEventListener('click', () => {
                    // Hide all
                    tabs.forEach(t => {
                        document.getElementById(t.target).classList.add('hidden');
                        document.getElementById(t.id).classList.remove('text-blue-600', 'border-blue-600');
                        document.getElementById(t.id).classList.add('border-transparent');
                    });

                    // Show current
                    document.getElementById(tab.target).classList.remove('hidden');
                    button.classList.add('text-blue-600', 'border-blue-600');
                    button.classList.remove('border-transparent');
                });
            });

            // Activate first tab by default
            document.getElementById('en-tab').click();

            const logoInput = document.querySelector('input[name="logo"]');
            const logoPreview = document.getElementById('logo-preview');
            const logoPlaceholder = document.getElementById('logo-placeholder');
            const namePreview = document.getElementById('preview-name');
            const nameEnPreview = document.getElementById('preview-name-en');
            const nameArPreview = document.getElementById('preview-name-ar');
            const nameEnInput = document.querySelector('input[name="name_en"]');
            const nameArInput = document.querySelector('input[name="name_ar"]');

            function updateLogoPreview(file) {
                if (!file) {
                    logoPreview.src = '';
                    logoPreview.classList.add('hidden');
                    logoPlaceholder.classList.remove('hidden');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    logoPreview.classList.remove('hidden');
                    logoPlaceholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }

            logoInput?.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                updateLogoPreview(file);
            });

            function updateNamePreviews() {
                namePreview.textContent = nameEnInput?.value || nameArInput?.value || namePreview.dataset.fallback || '';
                nameEnPreview.textContent = nameEnInput?.value || nameEnPreview.dataset.fallback || '';
                nameArPreview.textContent = nameArInput?.value || nameArPreview.dataset.fallback || '';
            }

            if (namePreview && !namePreview.dataset.fallback) {
                namePreview.dataset.fallback = namePreview.textContent;
            }
            if (nameEnPreview && !nameEnPreview.dataset.fallback) {
                nameEnPreview.dataset.fallback = nameEnPreview.textContent;
            }
            if (nameArPreview && !nameArPreview.dataset.fallback) {
                nameArPreview.dataset.fallback = nameArPreview.textContent;
            }

            nameEnInput?.addEventListener('input', updateNamePreviews);
            nameArInput?.addEventListener('input', updateNamePreviews);
        });
    </script>
@endsection
