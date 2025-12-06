@php
    $isEdit = isset($project);
    $gallery = $isEdit && $project->gallery ? $project->gallery : [];
    if (!is_array($gallery)) $gallery = [];

    $tabFieldMap = [
        'basic' => [
            'name_ar', 'name_en', 'project_area_value', 'project_area_unit', 'developer_id', 'sales_launch_date',
            'is_part_of_master_project', 'master_project_id', 'is_featured', 'is_top_project', 'include_in_sitemap',
            'status', 'is_active', 'sitemap', 'publish_status', 'hero_image', 'gallery', 'video_url', 'brochure'
        ],
        'description' => ['title_ar', 'description_ar', 'title_en', 'description_en', 'meta_title_ar', 'meta_description_ar'],
        'details' => ['min_price', 'max_price', 'min_bua', 'max_bua', 'delivery_year', 'total_units'],
        'location' => ['country_id', 'region_id', 'city_id', 'district_id', 'lat', 'lng', 'map_polygon'],
        'amenities' => ['amenities'],
        'faq' => ['faqs', 'faqs.*'],
    ];

    $activeTab = 'basic';
    if ($errors->any()) {
        foreach ($tabFieldMap as $tab => $fields) {
            foreach ($fields as $field) {
                if ($errors->has($field)) {
                    $activeTab = $tab;
                    break 2;
                }
            }
        }
    }
@endphp

<div x-data="projectForm({{ $isEdit ? 'true' : 'false' }}, {{ $isEdit ? $project->id : 'null' }}, '{{ $activeTab }}')"
     x-init="initMap()"
     class="bg-white rounded-lg shadow-md p-6">

    <div class="mb-4">
        <ul class="nav nav-tabs flex flex-wrap" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'basic' }" @click="activeTab = 'basic'">
                    {{ __('admin.basic_info') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'description' }" @click="activeTab = 'description'">
                    {{ __('admin.description') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'details' }" @click="activeTab = 'details'">
                    {{ __('admin.project_details_pricing') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'location' }" @click="activeTab = 'location'">
                    {{ __('admin.project_location') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'amenities' }" @click="activeTab = 'amenities'">
                    {{ __('admin.services_amenities') ?? 'Services & Amenities' }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'models' }" @click="activeTab = 'models'">
                    {{ __('admin.property_models') ?? 'Property Models' }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'faq' }" @click="activeTab = 'faq'">
                    FAQ
                </button>
            </li>
        </ul>
    </div>

    <form action="{{ $isEdit ? route('admin.projects.update', $project->id) : route('admin.projects.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="project-form">
        @csrf
        @if($isEdit) @method('PUT') @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">{{ __('admin.correct_errors') }}</strong>
                <ul class="mt-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="tab-content">
            <!-- TAB 1: Basic Info -->
            <div x-show="activeTab === 'basic'" x-cloak class="tab-pane fade show active space-y-6">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2">{{ __('admin.basic_info') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_name_ar') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $project->name_ar ?? '') }}" required class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_name_en') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name_en" value="{{ old('name_en', $project->name_en ?? '') }}" required class="w-full rounded border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.developer') }}</label>
                        <select name="developer_id" class="w-full rounded border-gray-300 p-2">
                            <option value="">{{ __('admin.select_developer') }}</option>
                            @foreach($developers as $dev)
                                <option value="{{ $dev->id }}" {{ old('developer_id', $project->developer_id ?? '') == $dev->id ? 'selected' : '' }}>
                                    {{ $dev->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.sales_launch_date') }}</label>
                        <input type="date" name="sales_launch_date" value="{{ old('sales_launch_date', $isEdit && $project->sales_launch_date ? $project->sales_launch_date->format('Y-m-d') : '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div x-data="areaConverter({{ json_encode(old('project_area_value', $project->project_area_value ?? null)) }}, '{{ old('project_area_unit', $project->project_area_unit ?? 'sqm') }}')">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_area') }}</label>
                        <div class="flex space-x-2">
                            <input type="number" step="0.01" name="project_area_value" x-model="areaValue" value="{{ old('project_area_value', $project->project_area_value ?? '') }}" class="w-full rounded border-gray-300 p-2">
                            <select name="project_area_unit" x-model="areaUnit" class="rounded border-gray-300 p-2">
                                <option value="feddan">{{ __('admin.unit_feddan') }}</option>
                                <option value="sqm">{{ __('admin.unit_sqm') }}</option>
                            </select>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" x-text="conversionText"></p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <p class="font-bold text-gray-700 mb-2">{{ __('admin.part_of_master_project') ?? 'Is the project part of a larger project?' }}</p>
                    <div x-data="{ isMasterProject: '{{ old('is_part_of_master_project', $project->is_part_of_master_project ?? false) ? '1' : '0' }}' }">
                        <div class="flex items-center space-x-4 mb-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_part_of_master_project" value="0" x-model="isMasterProject" class="text-blue-600">
                                <span class="ml-2">{{ __('admin.no') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_part_of_master_project" value="1" x-model="isMasterProject" class="text-blue-600">
                                <span class="ml-2">{{ __('admin.yes') }}</span>
                            </label>
                        </div>
                        <div x-show="isMasterProject === '1'" class="transition-all">
                            <label class="block text-gray-700 font-bold mb-2">{{ __('admin.master_project') ?? 'Select master project' }}</label>
                            <select name="master_project_id" class="w-full rounded border-gray-300 p-2">
                                <option value="">{{ __('admin.select_master_project') ?? 'Choose a master project' }}</option>
                                @foreach($existingProjects as $existingProject)
                                    <option value="{{ $existingProject->id }}" {{ old('master_project_id', $project->master_project_id ?? '') == $existingProject->id ? 'selected' : '' }}>
                                        {{ $existingProject->name_en ?? $existingProject->name_ar ?? $existingProject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded border border-gray-200">
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('admin.flags') ?? 'Feature flags' }}</label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $project->is_featured ?? false) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2">{{ __('admin.featured_project') ?? 'Featured project' }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_top_project" value="1" {{ old('is_top_project', $project->is_top_project ?? false) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2">{{ __('admin.top_project') ?? 'Top project' }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="include_in_sitemap" value="1" {{ old('include_in_sitemap', $project->include_in_sitemap ?? true) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2">{{ __('admin.include_in_sitemap') ?? 'Include in sitemap' }}</span>
                        </label>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('admin.status') ?? 'Status' }}</label>
                        <select name="status" class="w-full rounded border-gray-300 p-2">
                            @foreach(['draft' => __('admin.draft'), 'published' => __('admin.published')] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $project->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $project->is_active ?? true) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-green-600">
                            <span class="ml-2">{{ __('admin.activate_project') }}</span>
                        </label>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded border border-gray-200 space-y-6">
                    <div>
                        <h4 class="text-md font-semibold text-gray-800 mb-2">{{ __('admin.media') ?? 'Media & Files' }}</h4>
                        <p class="text-sm text-gray-500 mb-4">{{ __('admin.videos_photos') }}</p>
                    </div>

                    <div class="bg-blue-50 p-4 rounded border border-blue-200">
                        <label class="block text-blue-800 font-bold mb-2">{{ __('admin.hero_image') }}</label>
                        @if($isEdit && $project->hero_image_url)
                            <div class="mb-2">
                                <img src="{{ Storage::url($project->hero_image_url) }}" class="h-40 w-auto object-cover rounded shadow">
                                <p class="text-xs text-gray-500 mt-1">{{ __('admin.current_image') }}</p>
                            </div>
                        @endif
                        <input type="file" name="hero_image" accept="image/*" {{ $isEdit ? '' : 'required' }} class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1">{{ __('admin.hero_image_help') }}</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.video_url') }}</label>
                        <input type="url" name="video_url" value="{{ old('video_url', $project->video_url ?? '') }}" class="w-full rounded border-gray-300 p-2" placeholder="https://youtube.com/...">
                    </div>

                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <label class="block text-gray-700 font-bold mb-4">{{ __('admin.gallery') }}</label>

                        @if(count($gallery) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            @foreach($gallery as $idx => $img)
                            <div class="border rounded bg-white p-2 relative" id="gallery-item-{{ $idx }}">
                                <img src="{{ Storage::url($img['path']) }}" class="w-full h-32 object-cover rounded mb-2">

                                <div class="space-y-2">
                                    <input type="hidden" name="gallery_data[{{ $idx }}][path]" value="{{ $img['path'] }}">
                                    <input type="text" name="gallery_data[{{ $idx }}][name]" value="{{ $img['name'] ?? '' }}" placeholder="{{ __('admin.image_name') }}" class="w-full text-xs p-1 border rounded">
                                    <input type="text" name="gallery_data[{{ $idx }}][alt]" value="{{ $img['alt'] ?? '' }}" placeholder="{{ __('admin.alt_text') }}" class="w-full text-xs p-1 border rounded">

                                    <label class="flex items-center space-x-2 text-xs cursor-pointer">
                                        <input type="radio" name="selected_hero" value="{{ $img['path'] }}"
                                               {{ ($project->hero_image_url ?? '') == $img['path'] ? 'checked' : '' }}
                                               class="text-blue-600">
                                        <span class="mr-1">{{ __('admin.set_as_hero') }}</span>
                                    </label>
                                </div>

                                <button type="button" onclick="document.getElementById('gallery-item-{{ $idx }}').remove()" class="absolute top-1 left-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700" title="{{ __('admin.delete_image') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div class="mt-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('admin.add_new_images') }}</label>
                            <input type="file" name="gallery[]" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                            <p class="text-xs text-gray-500 mt-1">{{ __('admin.gallery_help') }}</p>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded border border-green-200">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.brochure') ?? 'Brochure (PDF)' }}</label>
                        @if($isEdit && $project->brochure)
                            <p class="text-sm text-gray-600 mb-2">{{ __('admin.current_file') ?? 'Current file' }}: <a href="{{ Storage::url($project->brochure) }}" class="text-blue-600 underline" target="_blank">{{ basename($project->brochure) }}</a></p>
                        @endif
                        <input type="file" name="brochure" accept="application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="text-xs text-gray-500 mt-1">{{ __('admin.brochure_help') }}</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Description -->
            <div x-show="activeTab === 'description'" x-cloak class="tab-pane fade space-y-6">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-800">{{ __('admin.project_description_section') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('admin.project_description_helper') }}</p>
                </div>

                <div x-data="{ langTab: 'ar' }" class="space-y-4">
                    <div class="flex space-x-2">
                        <button type="button" @click="langTab = 'ar'" :class="langTab === 'ar' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded-md border">{{ __('admin.arabic') }}</button>
                        <button type="button" @click="langTab = 'en'" :class="langTab === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded-md border">{{ __('admin.english') }}</button>
                    </div>

                    <div x-show="langTab === 'ar'" class="space-y-4" x-cloak>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_title_ar') }}</label>
                            <input type="text" name="title_ar" value="{{ old('title_ar', $project->title_ar ?? '') }}" class="w-full rounded border-gray-300 p-2">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_description_ar') }}</label>
                            <textarea id="project_description_ar" name="description_ar" rows="10" class="w-full rounded border-gray-300 p-2 tinymce-project-description">{{ old('description_ar', $project->description_ar ?? $project->description_long ?? '') }}</textarea>
                        </div>
                    </div>

                    <div x-show="langTab === 'en'" class="space-y-4" x-cloak>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_title_en') }}</label>
                            <input type="text" name="title_en" value="{{ old('title_en', $project->title_en ?? '') }}" class="w-full rounded border-gray-300 p-2">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_description_en') }}</label>
                            <textarea id="project_description_en" name="description_en" rows="10" class="w-full rounded border-gray-300 p-2 tinymce-project-description">{{ old('description_en', $project->description_en ?? $project->description_long ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="font-bold text-gray-700 mb-2">{{ __('admin.seo_settings') }}</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">{{ __('admin.meta_title') }}</label>
                            <input type="text" name="meta_title_ar" value="{{ old('meta_title_ar', $project->meta_title_ar ?? '') }}" class="w-full rounded border-gray-300 p-2">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">{{ __('admin.meta_description') }}</label>
                            <textarea name="meta_description_ar" rows="3" class="w-full rounded border-gray-300 p-2">{{ old('meta_description_ar', $project->meta_description_ar ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Details & Pricing -->
            <div x-show="activeTab === 'details'" x-cloak class="tab-pane fade space-y-6">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-800">{{ __('admin.project_details_pricing') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('admin.project_details_pricing_helper') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_min_price') }}</label>
                        <input type="number" name="min_price" value="{{ old('min_price', $project->min_price ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_max_price') }}</label>
                        <input type="number" name="max_price" value="{{ old('max_price', $project->max_price ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_min_bua') }}</label>
                        <input type="number" name="min_bua" value="{{ old('min_bua', $project->min_bua ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_max_bua') }}</label>
                        <input type="number" name="max_bua" value="{{ old('max_bua', $project->max_bua ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.total_units') }}</label>
                        <input type="number" name="total_units" value="{{ old('total_units', $project->total_units ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">{{ __('admin.delivery_year') }}</label>
                        <input type="number" name="delivery_year" value="{{ old('delivery_year', $project->delivery_year ?? '') }}" class="w-full rounded border-gray-300 p-2">
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <label class="block text-gray-700 font-bold mb-2">{{ __('admin.project_status') }}</label>
                    <select disabled class="w-full rounded border-gray-300 p-2 bg-gray-100 text-gray-700">
                        @foreach(['draft' => __('admin.draft'), 'published' => __('admin.published')] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $project->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.project_status_hint') ?? 'Status is managed in the Basic Info tab.' }}</p>
                </div>
            </div>

            <!-- TAB 4: Location & Map -->
            <div x-show="activeTab === 'location'" x-cloak class="tab-pane fade space-y-6">
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <h4 class="font-bold text-gray-700 mb-4">{{ __('admin.project_location') }}</h4>

                    <div class="mb-4 relative">
                        <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('admin.location_search') }}</label>
                        <p class="text-xs text-gray-500 mb-2">{{ __('admin.location_search_helper') }}</p>
                        <input type="text"
                               x-model="searchQuery"
                               @input.debounce.500ms="performSearch"
                               placeholder="{{ __('admin.location_search_placeholder') }}"
                               class="w-full rounded border-gray-300 p-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                        <div x-show="isSearching" class="absolute bg-white border border-gray-200 w-full mt-1 rounded shadow p-3 text-sm text-gray-500" style="display: none;">
                            {{ __('admin.searching') }}
                        </div>

                        <div x-show="!isSearching && searchResults.length > 0" class="absolute z-50 bg-white border border-gray-200 w-full mt-1 rounded shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                            <template x-for="result in searchResults" :key="`${result.type}-${result.city_id ?? ''}-${result.district_id ?? ''}`">
                                <div @click="selectSearchResult(result)" class="p-3 hover:bg-gray-100 cursor-pointer border-b text-sm">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-800" x-text="result.label"></p>
                                            <p class="text-xs text-gray-500" x-text="result.path"></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                            <span x-text="result.type === 'district' ? '{{ __('admin.location_type_district') }}' : '{{ __('admin.location_type_city') }}'"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="showCascading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 transition-all duration-300">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.country') }} <span class="text-red-500">*</span></label>
                            <select name="country_id" x-model="selectedCountry" @change="fetchRegions()" required class="w-full rounded border-gray-300 p-2 text-sm">
                                <option value="">{{ __('admin.select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name_local ?? $country->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.region') }} <span class="text-red-500">*</span></label>
                            <select name="region_id" x-model="selectedRegion" @change="fetchCities()" required class="w-full rounded border-gray-300 p-2 text-sm">
                                <option value="">{{ __('admin.select_region') }}</option>
                                <template x-for="region in regions" :key="region.id">
                                    <option :value="region.id" x-text="region.name_local || region.name_en"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.city') }} <span class="text-red-500">*</span></label>
                            <select name="city_id" x-model="selectedCity" @change="fetchDistricts()" required class="w-full rounded border-gray-300 p-2 text-sm">
                                <option value="">{{ __('admin.select_city') }}</option>
                                <template x-for="city in cities" :key="city.id">
                                    <option :value="city.id" x-text="city.name_local || city.name_en"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">{{ __('admin.district') }}</label>
                            <select name="district_id" x-model="selectedDistrict" required class="w-full rounded border-gray-300 p-2 text-sm">
                                <option value="">{{ __('admin.select_district') }}</option>
                                <template x-for="district in districts" :key="district.id">
                                    <option :value="district.id" x-text="district.name_local || district.name_en"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('admin.project_map') }}</h3>
                    <div id="project_map" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;" class="border border-gray-300"></div>

                    <input type="hidden" name="map_polygon" id="map_polygon" value="{{ old('map_polygon', json_encode($project->map_polygon ?? null)) }}">
                    <input type="hidden" name="lat" id="lat" value="{{ old('lat', $project->lat ?? '') }}">
                    <input type="hidden" name="lng" id="lng" value="{{ old('lng', $project->lng ?? '') }}">

                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.map_instruction') }}</p>
                </div>
            </div>

            <!-- TAB 5: Services & Amenities -->
            <div x-show="activeTab === 'amenities'" x-cloak class="tab-pane fade space-y-6">
                <div class="flex items-center justify-between border-b pb-2 mb-3">
                    <h3 class="text-lg font-bold text-gray-800">{{ __('admin.services_amenities') ?? 'Services & Amenities' }}</h3>
                    <p class="text-sm text-gray-500">{{ __('admin.project_details_pricing_helper') }}</p>
                </div>

                @php
                    $selectedAmenities = $isEdit ? $project->amenities->pluck('id')->toArray() : [];
                    $amenityGroups = ($amenities instanceof \Illuminate\Support\Collection && $amenities->first() instanceof \Illuminate\Support\Collection)
                        ? $amenities
                        : collect(['amenities' => $amenities]);
                @endphp

                <div class="space-y-4">
                    @forelse($amenityGroups as $groupKey => $groupAmenities)
                        <div>
                            <p class="text-sm font-semibold text-gray-700 capitalize mb-2">{{ str_replace('_', ' ', $groupKey) }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded border">
                                @foreach($groupAmenities as $amenity)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                               {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm">{{ $amenity->name_local ?? $amenity->name_en }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No amenities available.</p>
                    @endforelse
                </div>
            </div>

            <!-- TAB 6: Property Models -->
            <div x-show="activeTab === 'models'" x-cloak class="tab-pane fade space-y-6">
                @if($isEdit)
                    @include('admin.projects.partials.property-models-section', ['project' => $project])
                @else
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
                        <p class="font-semibold">{{ __('admin.save_project_first') ?? 'Save the project first to add property models.' }}</p>
                    </div>
                @endif
            </div>

            <!-- TAB 7: FAQ -->
            <div x-show="activeTab === 'faq'" x-cloak class="tab-pane fade space-y-6">
                @php
                    $faqItems = old('faqs');
                    if ($faqItems === null && isset($project)) {
                        $faqItems = $project->faqs->toArray();
                    }
                    $faqItems = $faqItems ?? [];
                @endphp

                <div class="flex items-center justify-between border-b pb-2 mb-4">
                    <h4 class="text-lg font-bold text-gray-800">FAQ</h4>
                    <button type="button" id="add-faq-item" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded shadow">
                        + {{ __('admin.add') ?? 'Add FAQ item' }}
                    </button>
                </div>

                <div id="faq-items" class="space-y-4">
                    @foreach($faqItems as $index => $faq)
                        <div class="faq-item border rounded-lg p-4 bg-gray-50 space-y-3">
                            <input type="hidden" name="faqs[{{ $index }}][id]" value="{{ $faq['id'] ?? '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question (Arabic)</label>
                                    <input type="text" name="faqs[{{ $index }}][question_ar]" value="{{ $faq['question_ar'] ?? '' }}" class="w-full rounded border-gray-300 p-2" placeholder="اكتب السؤال">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question (English)</label>
                                    <input type="text" name="faqs[{{ $index }}][question_en]" value="{{ $faq['question_en'] ?? '' }}" class="w-full rounded border-gray-300 p-2" placeholder="Enter question">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Answer (Arabic)</label>
                                    <textarea name="faqs[{{ $index }}][answer_ar]" rows="3" class="w-full rounded border-gray-300 p-2">{{ $faq['answer_ar'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Answer (English)</label>
                                    <textarea name="faqs[{{ $index }}][answer_en]" rows="3" class="w-full rounded border-gray-300 p-2">{{ $faq['answer_en'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
                                    <input type="number" name="faqs[{{ $index }}][sort_order]" value="{{ $faq['sort_order'] ?? $index }}" class="w-32 rounded border-gray-300 p-2">
                                </div>
                                <button type="button" class="remove-faq text-red-600 hover:text-red-800 text-sm font-semibold">{{ __('admin.delete') ?? 'Remove' }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <template id="faq-item-template">
                    <div class="faq-item border rounded-lg p-4 bg-gray-50 space-y-3">
                        <input type="hidden" data-name="faqs[__INDEX__][id]" value="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Question (Arabic)</label>
                                <input type="text" data-name="faqs[__INDEX__][question_ar]" class="w-full rounded border-gray-300 p-2" placeholder="اكتب السؤال">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Question (English)</label>
                                <input type="text" data-name="faqs[__INDEX__][question_en]" class="w-full rounded border-gray-300 p-2" placeholder="Enter question">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Answer (Arabic)</label>
                                <textarea data-name="faqs[__INDEX__][answer_ar]" rows="3" class="w-full rounded border-gray-300 p-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Answer (English)</label>
                                <textarea data-name="faqs[__INDEX__][answer_en]" rows="3" class="w-full rounded border-gray-300 p-2"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
                                <input type="number" data-name="faqs[__INDEX__][sort_order]" data-sort-default="__INDEX__" class="w-32 rounded border-gray-300 p-2">
                            </div>
                            <button type="button" class="remove-faq text-red-600 hover:text-red-800 text-sm font-semibold">{{ __('admin.delete') ?? 'Remove' }}</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-end mt-8 pt-4 border-t border-gray-200">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded shadow">
                {{ $isEdit ? __('admin.save_changes') : __('admin.save_project') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initProjectDescriptionEditors();
        initFaqForm();
    });

    function initProjectDescriptionEditors() {
        if (typeof tinymce === 'undefined') {
            return;
        }

        tinymce.init({
            selector: 'textarea.tinymce-project-description',
            plugins: 'link lists code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
            height: 300,
            menubar: false,
            branding: false,
            directionality: document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr',
        });
    }

    function initFaqForm() {
        const faqContainer = document.getElementById('faq-items');
        const template = document.getElementById('faq-item-template');
        const addButton = document.getElementById('add-faq-item');

        if (!faqContainer || !template || !addButton) {
            return;
        }

        let nextIndex = faqContainer.querySelectorAll('.faq-item').length;

        addButton.addEventListener('click', () => {
            const clone = document.importNode(template.content, true);
            clone.querySelectorAll('[data-name]').forEach((element) => {
                element.name = element.dataset.name.replace('__INDEX__', nextIndex);
            });
            clone.querySelectorAll('[data-sort-default]').forEach((element) => {
                element.value = element.dataset.sortDefault.replace('__INDEX__', nextIndex);
            });

            faqContainer.appendChild(clone);
            nextIndex++;
        });

        faqContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-faq')) {
                const item = event.target.closest('.faq-item');
                if (item) {
                    item.remove();
                }
            }
        });
    }

    function areaConverter(initialValue, initialUnit) {
        return {
            areaValue: initialValue || '',
            areaUnit: initialUnit || 'sqm',
            get conversionText() {
                if (!this.areaValue) return '';
                if (this.areaUnit === 'feddan') {
                    return `${(this.areaValue * 4200).toLocaleString()} sqm`;
                }
                return `${(this.areaValue / 4200).toFixed(2)} feddan`;
            }
        };
    }

    function projectForm(isEdit, projectId, defaultTab) {
        return {
            activeTab: defaultTab || 'basic',
            searchQuery: '',
            isSearching: false,
            searchResults: [],
            showCascading: Boolean({{ old('country_id', $project->country_id ?? '') ? 'true' : 'false' }}),
            selectedCountry: '{{ old('country_id', $project->country_id ?? '') }}',
            selectedRegion: '{{ old('region_id', $project->region_id ?? '') }}',
            selectedCity: '{{ old('city_id', $project->city_id ?? '') }}',
            selectedDistrict: '{{ old('district_id', $project->district_id ?? '') }}',
            regions: [],
            cities: [],
            districts: [],
            locationSearchUrl: '{{ route('admin.locations.search') }}',

            async init() {
                if (this.selectedCountry) {
                    this.fetchRegions(this.selectedRegion).then(() => {
                        if (this.selectedRegion) {
                            this.fetchCities(this.selectedCity).then(() => {
                                if (this.selectedCity) {
                                    this.fetchDistricts(this.selectedDistrict);
                                }
                            });
                        }
                    });
                }
            },

            async performSearch() {
                const query = this.searchQuery.trim();
                if (query.length < 2) {
                    this.searchResults = [];
                    return;
                }

                this.isSearching = true;
                try {
                    let res = await fetch(`${this.locationSearchUrl}?q=${encodeURIComponent(query)}`);
                    this.searchResults = await res.json();
                } catch (error) {
                    console.error('Location search failed', error);
                    this.searchResults = [];
                } finally {
                    this.isSearching = false;
                }
            },

            async selectSearchResult(result) {
                this.searchQuery = result.label;
                this.searchResults = [];
                this.showCascading = true;

                this.selectedCountry = result.country_id || '';
                await this.fetchRegions(result.region_id || '');
                await this.fetchCities(result.city_id || '');
                await this.fetchDistricts(result.district_id || '');
                this.selectedDistrict = result.district_id || '';

                if (result.lat && result.lng && window.projectMapControls) {
                    window.projectMapControls.focus(result.lat, result.lng);
                }
            },

            async fetchRegions(preset = '') {
                if(!this.selectedCountry) {
                    this.regions = [];
                    this.selectedRegion = '';
                    return;
                }
                let res = await fetch(`/admin/locations/countries/${this.selectedCountry}`);
                let data = await res.json();
                this.regions = data.regions || [];
                if (preset) {
                    this.selectedRegion = preset;
                } else if (!this.regions.some(region => String(region.id) === String(this.selectedRegion))) {
                    this.selectedRegion = '';
                }
            },
            async fetchCities(preset = '') {
                if(!this.selectedRegion) {
                    this.cities = [];
                    this.selectedCity = '';
                    return;
                }
                let res = await fetch(`/admin/locations/regions/${this.selectedRegion}`);
                let data = await res.json();
                this.cities = data.cities || [];
                if (preset) {
                    this.selectedCity = preset;
                } else if (!this.cities.some(city => String(city.id) === String(this.selectedCity))) {
                    this.selectedCity = '';
                }
            },
            async fetchDistricts(preset = '') {
                if(!this.selectedCity) {
                    this.districts = [];
                    this.selectedDistrict = '';
                    return;
                }
                let res = await fetch(`/admin/locations/cities/${this.selectedCity}`);
                let data = await res.json();
                this.districts = data.districts || [];
                if (preset) {
                    this.selectedDistrict = preset;
                } else if (!this.districts.some(district => String(district.id) === String(this.selectedDistrict))) {
                    this.selectedDistrict = '';
                }
            }
        }
    }

    function initMap() {
        var defaultLat = 30.0444;
        var defaultLng = 31.2357;

        var lat = parseFloat(document.getElementById('lat').value) || defaultLat;
        var lng = parseFloat(document.getElementById('lng').value) || defaultLng;

        var map = L.map('project_map').setView([lat, lng], 10);
        window.projectMapControls = {
            focus: function(lat, lng) {
                map.setView([lat, lng], 14);
                marker.setLatLng([lat, lng]);
            }
        };

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', function (e) {
            var latlng = marker.getLatLng();
            document.getElementById('lat').value = latlng.lat;
            document.getElementById('lng').value = latlng.lng;
        });

        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
                polygon: true,
                polyline: false,
                rectangle: false,
                circle: false,
                marker: false,
                circlemarker: false
            },
            edit: {
                featureGroup: drawnItems
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            drawnItems.addLayer(e.layer);
            document.getElementById('map_polygon').value = JSON.stringify(e.layer.toGeoJSON());
        });

        map.on(L.Draw.Event.EDITED, function (e) {
            e.layers.eachLayer(function (layer) {
                document.getElementById('map_polygon').value = JSON.stringify(layer.toGeoJSON());
            });
        });

        var polygonData = document.getElementById('map_polygon').value;
        if (polygonData) {
            try {
                var geojson = JSON.parse(polygonData);
                var polygon = L.geoJSON(geojson).addTo(drawnItems);
                map.fitBounds(polygon.getBounds());
            } catch (error) {
                console.error('Invalid polygon data', error);
            }
        }
    }
</script>
@endpush
