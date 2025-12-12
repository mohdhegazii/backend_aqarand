@extends('admin.layouts.app')

@section('header')
    {{ app()->getLocale() === 'ar' ? 'الأماكن المميزة والقريبة' : 'Featured / Nearby Places' }}
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6" x-data="featuredPlacesManager()">

    <!-- Debug Output (Visible for troubleshooting as requested) -->
    @if(config('app.debug'))
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded text-xs font-mono overflow-auto" style="max-height: 200px;">
        <strong>Debug Info:</strong>
        <div x-text="'Place SubCat: ' + placeData.sub_category_id"></div>
        <div x-text="'Selected MainCat: ' + selectedMainCategory"></div>
        <div x-text="'Filtered SubCats Count: ' + filteredSubCategories.length"></div>
        <div>Filtered IDs: <span x-text="filteredSubCategories.map(s => s.id).join(', ')"></span></div>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8 rtl:space-x-reverse" aria-label="Tabs">
            <button @click="activeTab = 'main-categories'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'main-categories', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'main-categories' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                {{ app()->getLocale() === 'ar' ? 'التصنيفات الرئيسية' : 'Main Categories' }}
            </button>

            <button @click="activeTab = 'sub-categories'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'sub-categories', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'sub-categories' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                {{ app()->getLocale() === 'ar' ? 'التصنيفات الفرعية' : 'Sub Categories' }}
            </button>

            <button @click="activeTab = 'places'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'places', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'places' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                {{ app()->getLocale() === 'ar' ? 'الأماكن المميزة' : 'Featured Places' }}
            </button>
        </nav>
    </div>

    <!-- Tab 1: Main Categories -->
    <div x-show="activeTab === 'main-categories'" class="space-y-6">
        <!-- Create/Edit Form -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="mainCatEditMode ? '{{ app()->getLocale() === 'ar' ? 'تعديل تصنيف رئيسي' : 'Edit Main Category' }}' : '{{ app()->getLocale() === 'ar' ? 'إضافة تصنيف رئيسي' : 'Add Main Category' }}'"></h3>

            <form :action="mainCatFormAction" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="mainCatEditMode ? 'PUT' : 'POST'">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_ar')</label>
                        <input type="text" name="name_ar" x-model="mainCatData.name_ar" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_en')</label>
                        <input type="text" name="name_en" x-model="mainCatData.name_en" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'ar' ? 'اسم الأيقونة (مثال: bi-hospital)' : 'Icon Name (e.g. bi-hospital)' }}</label>
                        <input type="text" name="icon_name" x-model="mainCatData.icon_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">{{ app()->getLocale() === 'ar' ? 'استخدم فئات Bootstrap Icons' : 'Use Bootstrap Icons classes' }}</p>
                    </div>
                    <div>
                        <label class="flex items-center mt-6">
                            <input type="checkbox" name="is_active" x-model="mainCatData.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">@lang('admin.is_active')</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" x-text="mainCatEditMode ? '@lang('admin.update')' : '@lang('admin.create')'"></button>
                    <button type="button" x-show="mainCatEditMode" @click="resetMainCatForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">@lang('admin.cancel')</button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('admin.name')</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($mainCategories as $category)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <i class="bi {{ $category->icon_name }} text-xl"></i>
                                <span class="ml-2 text-xs text-gray-400">{{ $category->icon_name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                <button type="button" @click='editMainCategory(@json($category, JSON_HEX_APOS))' class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</button>
                                <form action="{{ route('admin.featured-places.main-categories.destroy', $category->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">@lang('admin.delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Sub Categories -->
    <div x-show="activeTab === 'sub-categories'" class="space-y-6" style="display: none;">
        <!-- Create/Edit Form -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="subCatEditMode ? '{{ app()->getLocale() === 'ar' ? 'تعديل تصنيف فرعي' : 'Edit Sub Category' }}' : '{{ app()->getLocale() === 'ar' ? 'إضافة تصنيف فرعي' : 'Add Sub Category' }}'"></h3>

            <form :action="subCatFormAction" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="subCatEditMode ? 'PUT' : 'POST'">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'ar' ? 'التصنيف الرئيسي' : 'Main Category' }}</label>
                        <select name="main_category_id" x-model="subCatData.main_category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر...' : 'Select...' }}</option>
                            @foreach($mainCategories as $mc)
                                <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_ar')</label>
                        <input type="text" name="name_ar" x-model="subCatData.name_ar" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_en')</label>
                        <input type="text" name="name_en" x-model="subCatData.name_en" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="flex items-center mt-6">
                            <input type="checkbox" name="is_active" x-model="subCatData.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">@lang('admin.is_active')</span>
                        </label>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" x-text="subCatEditMode ? '@lang('admin.update')' : '@lang('admin.create')'"></button>
                    <button type="button" x-show="subCatEditMode" @click="resetSubCatForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">@lang('admin.cancel')</button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Main Category</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($subCategories as $sub)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sub->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sub->mainCategory->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $sub->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sub->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                <button type="button" @click='editSubCategory(@json($sub, JSON_HEX_APOS))' class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</button>
                                <form action="{{ route('admin.featured-places.sub-categories.destroy', $sub->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">@lang('admin.delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Featured Places -->
    <div x-show="activeTab === 'places'" class="space-y-6" style="display: none;">
        <!-- Create/Edit Form -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="placeEditMode ? '{{ app()->getLocale() === 'ar' ? 'تعديل مكان مميز' : 'Edit Featured Place' }}' : '{{ app()->getLocale() === 'ar' ? 'إضافة مكان مميز' : 'Add Featured Place' }}'"></h3>

            <form :action="placeFormAction" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="placeEditMode ? 'PUT' : 'POST'">

                <!-- Category Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 border-b border-gray-200 pb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'ar' ? 'التصنيف الرئيسي' : 'Main Category' }}</label>
                        <select name="main_category_id" x-model="selectedMainCategory" @change="filterSubCategories()" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر...' : 'Select...' }}</option>
                            @foreach($mainCategories as $mc)
                                <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                            @endforeach
                        </select>
                        @error('main_category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'ar' ? 'التصنيف الفرعي' : 'Sub Category' }}</label>
                        <select name="sub_category_id" x-model="placeData.sub_category_id" :disabled="!selectedMainCategory" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر...' : 'Select...' }}</option>
                            <template x-for="sub in filteredSubCategories" :key="sub.id">
                                <option :value="sub.id" x-text="{{ app()->getLocale() === 'ar' ? 'sub.name_ar' : 'sub.name_en' }}"></option>
                            </template>
                        </select>
                        @error('sub_category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Location Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 border-b border-gray-200 pb-4">
                     <!-- Country -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.country')</label>
                        <select name="country_id" id="fp_country_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                             <option value="">@lang('admin.select_country')</option>
                             @foreach($countries as $country)
                                 <option value="{{ $country->id }}" data-lat="{{ $country->lat }}" data-lng="{{ $country->lng }}">{{ $country->display_name }}</option>
                             @endforeach
                        </select>
                        @error('country_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                     </div>

                     <!-- Region -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.region')</label>
                        <select name="region_id" id="fp_region_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required disabled>
                             <option value="">@lang('admin.select_region')</option>
                        </select>
                        @error('region_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                     </div>

                     <!-- City -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.city')</label>
                        <select name="city_id" id="fp_city_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required disabled>
                             <option value="">@lang('admin.select_city')</option>
                        </select>
                        @error('city_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                     </div>

                     <!-- District -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.district') (@lang('admin.optional'))</label>
                        <select name="district_id" id="fp_district_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" disabled>
                             <option value="">@lang('admin.select_district')</option>
                        </select>
                        @error('district_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                     </div>
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_ar')</label>
                        <input type="text" name="name_ar" x-model="placeData.name_ar" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name_ar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_en')</label>
                        <input type="text" name="name_en" x-model="placeData.name_en" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name_en')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="flex items-center mt-6">
                            <input type="checkbox" name="is_active" x-model="placeData.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">@lang('admin.is_active')</span>
                        </label>
                    </div>
                </div>

                <!-- Map -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'الخريطة' : 'Map' }}</label>

                    <x-location.map
                        mapId="featured-places-map"
                        :entityLevel="'project'"
                        :lockToEgypt="true"
                        :readOnly="false"
                        :searchable="true"
                        :autoInit="true"
                        inputLatName="point_lat"
                        inputLngName="point_lng"
                        inputPolygonName="polygon_geojson"
                        :apiPolygonUrl="route('admin.location-polygons')"
                        onMapInit="setupFeaturedPlacesMap"
                    />
                </div>

                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" x-text="placeEditMode ? '@lang('admin.update')' : '@lang('admin.create')'"></button>
                    <button type="button" x-show="placeEditMode" @click="resetPlaceForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">@lang('admin.cancel')</button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($places as $place)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $place->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $place->mainCategory->name ?? '-' }} / {{ $place->subCategory->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $place->city->name ?? '' }}
                                @if($place->district)
                                    - {{ $place->district->name }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $place->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $place->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                @php
                                    $placeData = $place->only(["id", "main_category_id", "sub_category_id", "country_id", "region_id", "city_id", "district_id", "name_ar", "name_en", "is_active", "point_lat", "point_lng", "polygon_geojson"]);
                                @endphp
                                <button type="button" @click='editPlace(@json($placeData, JSON_HEX_APOS))' class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</button>
                                <form action="{{ route('admin.featured-places.places.destroy', $place->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">@lang('admin.delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $places->links() }}
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Define the map initialization callback globally
    window.setupFeaturedPlacesMap = function(map) {
        if (!map) return;

        // Use the centralized logic to bind dropdowns to map
        map.setupLocationDropdowns({
            country: '#fp_country_id',
            region: '#fp_region_id',
            city: '#fp_city_id',
            district: '#fp_district_id'
        });
    };

    document.addEventListener('alpine:init', () => {
        Alpine.data('featuredPlacesManager', () => ({
            activeTab: 'main-categories',
            mainCatEditMode: false,
            mainCatFormAction: "{{ route('admin.featured-places.main-categories.store') }}",
            mainCatData: { id: null, name_ar: '', name_en: '', icon_name: '', is_active: true },
            subCatEditMode: false,
            subCatFormAction: "{{ route('admin.featured-places.sub-categories.store') }}",
            subCatData: { id: null, main_category_id: '', name_ar: '', name_en: '', is_active: true },
            placeEditMode: false,
            placeFormAction: "{{ route('admin.featured-places.places.store') }}",
            selectedMainCategory: '',
            allSubCategories: @json($subCategories),
            filteredSubCategories: [],
            placeData: { id: null, sub_category_id: '', name_ar: '', name_en: '', is_active: true },

            get map() { return window['map_featured-places-map']; },

            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');
                if (tab && ['main-categories', 'sub-categories', 'places'].includes(tab)) this.activeTab = tab;
                this.filterSubCategories();
                this.$watch('activeTab', (value) => {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', value);
                    window.history.pushState({}, '', url);
                    if (value === 'places') {
                        this.$nextTick(() => {
                            setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 200);
                        });
                    }
                });
            },

            resetMainCatForm() { this.mainCatEditMode = false; this.mainCatFormAction = "{{ route('admin.featured-places.main-categories.store') }}"; this.mainCatData = { id: null, name_ar: '', name_en: '', icon_name: '', is_active: true }; },
            editMainCategory(cat) { this.activeTab = 'main-categories'; this.mainCatEditMode = true; this.mainCatFormAction = "{{ route('admin.featured-places.main-categories.update', '0') }}".replace('/0', '/' + cat.id); this.mainCatData = { id: cat.id, name_ar: cat.name_ar, name_en: cat.name_en, icon_name: cat.icon_name, is_active: cat.is_active }; },
            resetSubCatForm() { this.subCatEditMode = false; this.subCatFormAction = "{{ route('admin.featured-places.sub-categories.store') }}"; this.subCatData = { id: null, main_category_id: '', name_ar: '', name_en: '', is_active: true }; },
            editSubCategory(sub) { this.activeTab = 'sub-categories'; this.subCatEditMode = true; this.subCatFormAction = "{{ route('admin.featured-places.sub-categories.update', '0') }}".replace('/0', '/' + sub.id); this.subCatData = { id: sub.id, main_category_id: sub.main_category_id, name_ar: sub.name_ar, name_en: sub.name_en, is_active: sub.is_active }; },

            filterSubCategories() {
                if (!this.selectedMainCategory) { this.filteredSubCategories = []; this.placeData.sub_category_id = ''; return; }
                this.filteredSubCategories = this.allSubCategories.filter(sub => sub.main_category_id == this.selectedMainCategory);
                if (!this.filteredSubCategories.some(sub => sub.id == this.placeData.sub_category_id)) this.placeData.sub_category_id = '';
            },
            resetPlaceForm() {
                this.placeEditMode = false;
                this.placeFormAction = "{{ route('admin.featured-places.places.store') }}";
                this.selectedMainCategory = '';
                this.filterSubCategories();
                this.placeData = { id: null, sub_category_id: '', name_ar: '', name_en: '', is_active: true };
                document.getElementById('fp_country_id').value = '';
                document.getElementById('fp_country_id').dispatchEvent(new Event('change'));
                if(this.map) {
                    this.map.invalidateSize();
                    this.map.setView([30.0444, 31.2357], 6);
                    if(this.map.setPolygon) this.map.setPolygon(null);
                    if(this.map.updateMarker) this.map.updateMarker(30.0444, 31.2357);
                }
            },
            async editPlace(place) {
                if (!place) return;
                const currentPlace = place;
                try {
                    this.activeTab = 'places';
                    this.placeEditMode = true;
                    this.placeFormAction = "{{ route('admin.featured-places.places.update', '__PLACE_ID__') }}".replace('__PLACE_ID__', currentPlace.id);
                    this.selectedMainCategory = currentPlace.main_category_id;
                    this.placeData = {
                        id: currentPlace.id,
                        sub_category_id: currentPlace.sub_category_id || '',
                        name_ar: currentPlace.name_ar,
                        name_en: currentPlace.name_en,
                        is_active: currentPlace.is_active
                    };
                    this.filterSubCategories();

                    const countryId = currentPlace.country_id;
                    const regionId = currentPlace.region_id;
                    const cityId = currentPlace.city_id;
                    const districtId = currentPlace.district_id;

                    if (countryId) {
                        const countrySelect = document.getElementById('fp_country_id');
                        if (countrySelect) {
                            countrySelect.value = countryId;
                            if (window.loadRegions) await window.loadRegions(countryId);
                            const regionSelect = document.getElementById('fp_region_id');
                            if (regionSelect) {
                                regionSelect.value = regionId;
                                if (window.loadCities && regionId) await window.loadCities(regionId);
                                const citySelect = document.getElementById('fp_city_id');
                                if (citySelect) {
                                    citySelect.value = cityId;
                                    if (window.loadDistricts && cityId) await window.loadDistricts(cityId);
                                    const districtSelect = document.getElementById('fp_district_id');
                                    if (districtSelect && districtId) districtSelect.value = districtId;
                                }
                            }
                        }
                    }
                } catch (e) { console.error('Error populating edit form:', e); }

                this.$nextTick(() => {
                    setTimeout(() => {
                        try {
                            const mapInstance = this.map;
                            if(mapInstance) {
                                mapInstance.invalidateSize();
                                if (currentPlace.point_lat && currentPlace.point_lng) {
                                    const lat = parseFloat(currentPlace.point_lat);
                                    const lng = parseFloat(currentPlace.point_lng);
                                    if(mapInstance.flyToLocation) mapInstance.flyToLocation(lat, lng, 15);
                                    else mapInstance.setView([lat, lng], 15);
                                    // Update marker
                                    if (mapInstance.updateMarker) mapInstance.updateMarker(lat, lng);
                                }
                                let geoJson = currentPlace.polygon_geojson;
                                if (typeof geoJson === 'string' && geoJson.trim() !== '') {
                                     try { geoJson = JSON.parse(geoJson); } catch(e) {}
                                }
                                if(mapInstance.setPolygon) mapInstance.setPolygon(geoJson);
                            }
                        } catch(mapError) { console.error('Error updating map:', mapError); }
                    }, 300);
                });
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('fp_country_id');
        const regionSelect = document.getElementById('fp_region_id');
        const citySelect = document.getElementById('fp_city_id');
        const districtSelect = document.getElementById('fp_district_id');

        async function fetchLocations(url) {
            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Network response');
                return await response.json();
            } catch (error) { console.error('Error fetching locations:', error); return []; }
        }

        function populateSelect(selectElement, items, placeholder) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
            if (Array.isArray(items)) {
                const isAr = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
                items.forEach(item => {
                    if (!item) return;
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = isAr ? (item.name_local || item.name_ar || item.name_en) : (item.name_en || item.name_local);
                    // Add data-lat/lng so map.setupLocationDropdowns can use them
                    if(item.lat && item.lng) { option.setAttribute('data-lat', item.lat); option.setAttribute('data-lng', item.lng); }
                    selectElement.appendChild(option);
                });
                selectElement.disabled = false;
            }
        }

        window.loadRegions = async function(countryId) { const response = await fetchLocations(`{{ url('admin/locations/regions') }}/${countryId}`); populateSelect(regionSelect, response.regions || [], "@lang('admin.select_region')"); citySelect.innerHTML = '<option value="">@lang('admin.select_city')</option>'; citySelect.disabled = true; districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true; }
        window.loadCities = async function(regionId) { const response = await fetchLocations(`{{ url('admin/locations/cities') }}/${regionId}`); populateSelect(citySelect, response.cities || [], "@lang('admin.select_city')"); districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true; }
        window.loadDistricts = async function(cityId) { const response = await fetchLocations(`{{ url('admin/locations/districts') }}/${cityId}`); populateSelect(districtSelect, response.districts || [], "@lang('admin.select_district')"); }

        // Event listeners for cascading loading (Map sync is handled by setupLocationDropdowns)
        countrySelect.addEventListener('change', async function() { if(this.value) { await window.loadRegions(this.value); } else { regionSelect.innerHTML = '<option value="">@lang('admin.select_region')</option>'; regionSelect.disabled = true; citySelect.innerHTML = '<option value="">@lang('admin.select_city')</option>'; citySelect.disabled = true; districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true; } });
        regionSelect.addEventListener('change', async function() { if(this.value) { await window.loadCities(this.value); } else { citySelect.innerHTML = '<option value="">@lang('admin.select_city')</option>'; citySelect.disabled = true; districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true; } });
        citySelect.addEventListener('change', async function() { if(this.value) { await window.loadDistricts(this.value); } else { districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true; } });
    });
</script>
@endpush
@endsection
