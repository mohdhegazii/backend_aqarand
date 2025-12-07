@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' ' . __('admin.developers'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'developers.update', ['developer' => $developer]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @php
                    $previewName = $developer->name ?? $developer->name_en ?? $developer->name_ar ?? __('admin.developers');
                    $previewAltEn = $developer->name_en;
                    $previewAltAr = $developer->name_ar;
                    $logoUrl = $developer->logo_url;
                @endphp

                <div class="mb-6 p-4 bg-gray-50 border rounded">
                    <div class="flex flex-col items-center text-center gap-3">
                        <div class="h-28 w-28 rounded border bg-white flex items-center justify-center overflow-hidden">
                            @if($logoUrl)
                                <img id="logo-preview" src="{{ $logoUrl }}" alt="{{ $previewName }}" class="h-full w-full object-contain" onerror="this.classList.add('hidden'); document.getElementById('logo-placeholder')?.classList.remove('hidden');">
                                <span id="logo-placeholder" class="hidden text-[11px] text-gray-400 text-center px-2">@lang('admin.logo')</span>
                            @else
                                <img id="logo-preview" src="" alt="{{ $previewName }}" class="hidden h-full w-full object-contain" onerror="this.classList.add('hidden'); document.getElementById('logo-placeholder')?.classList.remove('hidden');">
                                <span id="logo-placeholder" class="text-[11px] text-gray-400 text-center px-2">@lang('admin.logo')</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <h3 id="preview-name" class="text-lg font-semibold text-gray-800">{{ $previewName }}</h3>
                            <div id="preview-alt-en" class="text-sm text-gray-600">{{ $previewAltEn ?? __('admin.name_en') }}</div>
                            <div id="preview-alt-ar" class="text-sm text-gray-600">{{ $previewAltAr ?? __('admin.name_ar') }}</div>
                        </div>
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
                            <input id="name-en" type="text" name="name_en" value="{{ old('name_en', $developer->name_en) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_en')</label>
                            <textarea name="description_en" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_en', $developer->description_en) }}</textarea>
                        </div>

                        @include('admin.partials.seo_meta_box', ['locale' => 'en', 'seoMeta' => $developer->seoMeta])
                    </div>

                    <!-- Arabic Tab -->
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="ar" role="tabpanel" aria-labelledby="ar-tab">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.name_ar')</label>
                            <input id="name-ar" type="text" name="name_ar" value="{{ old('name_ar', $developer->name_ar) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.description_ar')</label>
                            <textarea name="description_ar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm" rows="6">{{ old('description_ar', $developer->description_ar) }}</textarea>
                        </div>

                        @include('admin.partials.seo_meta_box', ['locale' => 'ar', 'seoMeta' => $developer->seoMeta])
                    </div>
                </div>

                <div class="mt-6 border-t pt-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">@lang('admin.logo')</label>
                        <input id="logo-input" type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
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

            const nameEnInput = document.getElementById('name-en');
            const nameArInput = document.getElementById('name-ar');
            const previewName = document.getElementById('preview-name');
            const previewAltEn = document.getElementById('preview-alt-en');
            const previewAltAr = document.getElementById('preview-alt-ar');
            const logoInput = document.getElementById('logo-input');
            const logoPreview = document.getElementById('logo-preview');
            const logoPlaceholder = document.getElementById('logo-placeholder');
            const initialLogoUrl = @json($logoUrl);

            const updateTextPreview = () => {
                const mainName = nameEnInput?.value?.trim() || nameArInput?.value?.trim() || @json(__('admin.developers'));
                const altEn = nameEnInput?.value?.trim() || @json(__('admin.name_en'));
                const altAr = nameArInput?.value?.trim() || @json(__('admin.name_ar'));

                if (previewName) previewName.textContent = mainName;
                if (previewAltEn) previewAltEn.textContent = altEn;
                if (previewAltAr) previewAltAr.textContent = altAr;
            };

            const resetLogoPreview = () => {
                if (!logoPreview || !logoPlaceholder) return;
                logoPreview.classList.add('hidden');
                logoPlaceholder.classList.remove('hidden');
                logoPreview.removeAttribute('src');
            };

            const handleLogoChange = (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    resetLogoPreview();
                    return;
                }

                const objectUrl = URL.createObjectURL(file);
                logoPreview.src = objectUrl;
                logoPreview.classList.remove('hidden');
                logoPlaceholder.classList.add('hidden');
            };

            if (initialLogoUrl && logoPreview) {
                logoPreview.src = initialLogoUrl;
                logoPreview.classList.remove('hidden');
                logoPlaceholder?.classList.add('hidden');
            }

            nameEnInput?.addEventListener('input', updateTextPreview);
            nameArInput?.addEventListener('input', updateTextPreview);
            logoInput?.addEventListener('change', handleLogoChange);

            updateTextPreview();
            // Activate first tab by default
            document.getElementById('en-tab').click();
        });
    </script>
@endsection
