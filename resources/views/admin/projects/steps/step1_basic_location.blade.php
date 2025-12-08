@php
    $isAr = app()->getLocale() === 'ar';
    $rawPartOfMaster = old('is_part_of_master_project', $project->is_part_of_master_project);
    $normalizedPartOfMaster = isset($rawPartOfMaster) && $rawPartOfMaster !== '' ? (string) $rawPartOfMaster : '';
@endphp

<div class="space-y-6">
    <h3 class="text-lg font-bold text-gray-800">{{ __('admin.projects.steps.basic') }} / {{ __('admin.projects.steps.location') }}</h3>

    <div x-show="step1Errors.length" x-cloak class="bg-red-50 border border-red-200 text-red-700 rounded p-3 space-y-1">
        <template x-for="(error, index) in step1Errors" :key="index">
            <div x-text="error" class="text-sm"></div>
        </template>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700">{{ $isAr ? 'اسم المشروع (عربي)' : 'Project Name (Arabic)' }}</label>
            <input type="text" name="name_ar" value="{{ old('name_ar', $project->name_ar ?? '') }}" required class="form-control form-control-lg w-full rounded border-gray-300" placeholder="{{ $isAr ? 'مثال: كمبوند المقصد العاصمة الإدارية' : 'Example: Al Maqsad New Capital (Arabic name)' }}" />
            @if($isAr)
                <p class="text-xs text-gray-500 mt-1">يُستخدم في الـ SEO العربي والعنوان التعريفي.</p>
            @else
                <p class="text-xs text-gray-500 mt-1">Used for SEO (Arabic version) and page title in Arabic.</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700">{{ $isAr ? 'اسم المشروع (إنجليزي)' : 'Project Name (English)' }}</label>
            <input type="text" name="name_en" value="{{ old('name_en', $project->name_en ?? '') }}" required class="form-control form-control-lg w-full rounded border-gray-300" placeholder="{{ $isAr ? 'مثال: Al Maqsad New Capital' : 'Example: Al Maqsad New Capital' }}" />
            @if($isAr)
                <p class="text-xs text-gray-500 mt-1">يُستخدم في الـ SEO الإنجليزي والعنوان التعريفي الإنجليزي.</p>
            @else
                <p class="text-xs text-gray-500 mt-1">Used for English SEO and slug/title.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700">{{ $isAr ? 'المطوّر' : 'Developer' }}</label>
            <select name="developer_id" class="w-full rounded border-gray-300" required>
                <option value="">-- {{ __('admin.select_developer') }} --</option>
                @foreach($developers as $dev)
                    <option value="{{ $dev->id }}" {{ old('developer_id', $project->developer_id ?? '') == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700">{{ $isAr ? 'تاريخ الإطلاق' : 'Launch Date' }}</label>
            <input type="date" name="launch_date" value="{{ old('launch_date', optional($project->launch_date ?? $project->sales_launch_date ?? null)->format('Y-m-d')) }}" class="w-full rounded border-gray-300" />
            @if($isAr)
                <p class="text-xs text-gray-500 mt-1">تاريخ إطلاق المشروع أو أول مرحلة.</p>
            @else
                <p class="text-xs text-gray-500 mt-1">Project launch date or first phase start.</p>
            @endif
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.part_of_master_project_question') }}</label>
        <select name="is_part_of_master_project" id="is_part_of_master_project" class="w-full rounded border-gray-300 mt-1" x-model="partOfMaster">
            <option value="">{{ __('admin.projects.select_project_type') }}</option>
            <option value="0" {{ $normalizedPartOfMaster === '0' ? 'selected' : '' }}>{{ __('admin.projects.project_type_standalone') }}</option>
            <option value="1" {{ $normalizedPartOfMaster === '1' ? 'selected' : '' }}>{{ __('admin.projects.project_type_phase') }}</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">{{ __('admin.projects.part_of_master_project_hint') }}</p>
    </div>

    <div x-show="partOfMaster === '1'" class="space-y-2" x-cloak>
        <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.master_project') }}</label>
        <select name="master_project_id" id="master_project_id" class="w-full rounded border-gray-300" x-bind:required="partOfMaster === '1'">
            <option value="">-- {{ __('admin.projects.master_project_placeholder') }} --</option>
            @foreach($existingProjects as $existingProject)
                <option
                    value="{{ $existingProject->id }}"
                    data-country="{{ $existingProject->country_id }}"
                    data-region="{{ $existingProject->region_id }}"
                    data-city="{{ $existingProject->city_id }}"
                    data-district="{{ $existingProject->district_id }}"
                    data-lat="{{ $existingProject->map_lat ?? $existingProject->lat }}"
                    data-lng="{{ $existingProject->map_lng ?? $existingProject->lng }}"
                    {{ old('master_project_id', $project->master_project_id ?? '') == $existingProject->id ? 'selected' : '' }}>
                    {{ $existingProject->name_en ?? $existingProject->name_ar ?? $existingProject->name }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500">{{ __('admin.projects.master_project_note') }}</p>
    </div>

    <div id="project-location-block" class="space-y-2" style="display:none;" x-cloak>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-700">{{ $isAr ? 'المحافظة / المدينة / الحي' : 'Governorate / City / District' }}</p>
            </div>
            <div class="text-xs text-gray-500" id="location_inherit_badge" hidden>
                {{ __('admin.projects.inherited_from_master') }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.search') }}: {{ __('admin.projects.city_district_project_search') }}</label>
                <input type="text" id="location_search" class="w-full rounded border-gray-300" placeholder="{{ __('admin.projects.city_district_project_search') }}" autocomplete="off">
                <div id="location_search_results" class="bg-white border border-gray-200 rounded mt-1 shadow-sm hidden"></div>
            </div>

            <input type="hidden" name="country_id" id="country_id" value="{{ old('country_id', $project->country_id ?? $defaultCountryId ?? '') }}">
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.region') }}</label>
                <select name="region_id" id="region_id" class="w-full rounded border-gray-300" data-location-select></select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.city') }}</label>
                <select name="city_id" id="city_id" class="w-full rounded border-gray-300" data-location-select></select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.district') }}</label>
                <select name="district_id" id="district_id" class="w-full rounded border-gray-300" data-location-select></select>
            </div>
        </div>

        <div class="space-y-2">
            <div class="hidden">
                <input type="hidden" name="map_lat" id="map_lat" value="{{ old('map_lat', $project->map_lat ?? $project->lat ?? '') }}">
                <input type="hidden" name="map_lng" id="map_lng" value="{{ old('map_lng', $project->map_lng ?? $project->lng ?? '') }}">
                <input type="hidden" name="map_zoom" id="map_zoom" value="{{ old('map_zoom', $project->map_zoom ?? 10) }}">
                <input type="hidden" name="map_polygon" id="map_polygon" value="{{ old('map_polygon', json_encode($project->map_polygon ?? null)) }}">
            </div>
            <x-admin.location-map
                mapId="project-map"
                :lat="old('map_lat', $project->map_lat ?? $project->lat ?? 30.0444)"
                :lng="old('map_lng', $project->map_lng ?? $project->lng ?? 31.2357)"
                :zoom="old('map_zoom', $project->map_zoom ?? 10)"
                entityLevel="project"
                :entityId="$project->id ?? null"
                polygonFieldSelector="#map_polygon"
                latFieldSelector="#map_lat"
                lngFieldSelector="#map_lng" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700">{{ $isAr ? 'مساحة المشروع (م² / فدان…)' : 'Project Area (m² / Acre…)' }}</label>
            <div class="flex space-x-2">
                <input type="number" step="0.01" name="project_area" value="{{ old('project_area', $project->project_area_value ?? '') }}" class="w-full rounded border-gray-300" />
                <select name="project_area_unit" class="rounded border-gray-300">
                    <option value="sqm" {{ old('project_area_unit', $project->project_area_unit ?? 'sqm') === 'sqm' ? 'selected' : '' }}>m²</option>
                    <option value="feddan" {{ old('project_area_unit', $project->project_area_unit ?? '') === 'feddan' ? 'selected' : '' }}>{{ __('admin.unit_feddan') }}</option>
                </select>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ $isAr ? 'الأساس بالفدان.' : 'Default unit is Feddan.' }}</p>
        </div>
    </div>
</div>
