<div class="space-y-6">
    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <h3 class="text-lg font-bold text-gray-800">البيانات الأساسية</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700">اسم المشروع (عربي)</label>
            <input
                type="text"
                name="name_ar"
                value="{{ old('name_ar', $project->name_ar ?? '') }}"
                required
                class="form-control form-control-lg w-full rounded border-gray-300"
                placeholder="مثال: كمبوند المقصد العاصمة الإدارية"
            />
            <p class="text-xs text-gray-500 mt-1">يُستخدم في الـ SEO العربي والعنوان التعريفي.</p>
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
                <p class="text-sm font-semibold text-gray-700">{{ $isAr ? 'المحافظة / المدينة / الحي / المشروع' : 'Governorate / City / District / Project' }}</p>
            </div>
            <div class="text-xs text-gray-500" id="location_inherit_badge" hidden>
                {{ __('admin.projects.inherited_from_master') }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.country') }}</label>
                <input type="hidden" name="country_id" id="country_id" value="{{ $defaultCountryId ?? old('country_id', $project->country_id ?? '') }}">
                <input type="text" readonly class="w-full rounded border-gray-200 bg-gray-50" value="{{ $isAr ? 'مصر (ثابت)' : 'Egypt (fixed)' }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.governorate') }}</label>
                <select name="region_id" id="region_id" class="w-full rounded border-gray-300" data-location-select required>
                    <option value="">{{ __('admin.select_region') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.city') }}</label>
                <select name="city_id" id="city_id" class="w-full rounded border-gray-300" data-location-select required>
                    <option value="">{{ __('admin.select_city') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.district_neighborhood') }} <span class="text-gray-500 text-xs">({{ $isAr ? 'اختياري' : 'Optional' }})</span></label>
                <select name="district_id" id="district_id" class="w-full rounded border-gray-300" data-location-select>
                    <option value="">{{ __('admin.select_district') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.location_project_optional') }}</label>
                <select name="location_project_id" id="location_project_id" class="w-full rounded border-gray-300" data-location-select>
                    <option value="">{{ __('admin.projects.select_location_project') }}</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">{{ $isAr ? 'مشروع مرتبط بالحي (إن وجد).' : 'Projects linked to the selected district (if any).' }}</p>
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
            <label class="block text-sm font-semibold text-gray-700">اسم المشروع (إنجليزي)</label>
            <input
                type="text"
                name="name_en"
                value="{{ old('name_en', $project->name_en ?? '') }}"
                required
                class="form-control form-control-lg w-full rounded border-gray-300"
                placeholder="مثال: Al Maqsad New Capital"
            />
            <p class="text-xs text-gray-500 mt-1">يُستخدم في الـ SEO الإنجليزي والعنوان التعريفي الإنجليزي.</p>
        </div>
    </div>
</div>