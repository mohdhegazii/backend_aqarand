@extends('admin.layouts.app')

@section('header')
    {{ app()->getLocale() === 'ar' ? 'الأماكن المميزة والقريبة' : 'Featured / Nearby Places' }}
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6" x-data="featuredPlacesManager()">

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
                                <button type="button" @click='editMainCategory(@json($category))' class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</button>
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
                                <button type="button" @click='editSubCategory(@json($sub))' class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</button>
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
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'ar' ? 'التصنيف الفرعي' : 'Sub Category' }}</label>
                        <select name="sub_category_id" x-model="placeData.sub_category_id" :disabled="!selectedMainCategory" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر...' : 'Select...' }}</option>
                            <template x-for="sub in filteredSubCategories" :key="sub.id">
                                <option :value="sub.id" x-text="{{ app()->getLocale() === 'ar' ? 'sub.name_ar' : 'sub.name_en' }}" :selected="sub.id == placeData.sub_category_id"></option>
                            </template>
                        </select>
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
                     </div>

                     <!-- Region -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.region')</label>
                        <select name="region_id" id="fp_region_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required disabled>
                             <option value="">@lang('admin.select_region')</option>
                        </select>
                     </div>

                     <!-- City -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.city')</label>
                        <select name="city_id" id="fp_city_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required disabled>
                             <option value="">@lang('admin.select_city')</option>
                        </select>
                     </div>

                     <!-- District -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.district') (@lang('admin.optional'))</label>
                        <select name="district_id" id="fp_district_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" disabled>
                             <option value="">@lang('admin.select_district')</option>
                        </select>
                     </div>
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_ar')</label>
                        <input type="text" name="name_ar" x-model="placeData.name_ar" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@lang('admin.name_en')</label>
                        <input type="text" name="name_en" x-model="placeData.name_en" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                                <button type="button" @click='editPlace(@json($place))' class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</button>
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
    document.addEventListener('alpine:init', () => {
        Alpine.data('featuredPlacesManager', () => ({
            activeTab: 'main-categories',

            // Main Category Data
            mainCatEditMode: false,
            mainCatFormAction: "{{ route('admin.featured-places.main-categories.store') }}",
            mainCatData: {
                id: null,
                name_ar: '',
                name_en: '',
                icon_name: '',
                is_active: true
            },

            // Sub Category Data
            subCatEditMode: false,
            subCatFormAction: "{{ route('admin.featured-places.sub-categories.store') }}",
            subCatData: {
                id: null,
                main_category_id: '',
                name_ar: '',
                name_en: '',
                is_active: true
            },

            // Place Data
            placeEditMode: false,
            placeFormAction: "{{ route('admin.featured-places.places.store') }}",
            selectedMainCategory: '',
            allSubCategories: @json($subCategories),
            filteredSubCategories: [],
            placeData: {
                id: null,
                sub_category_id: '',
                name_ar: '',
                name_en: '',
                is_active: true
            },

            get map() {
                return window['map_featured-places-map'];
            },

            init() {
                // Initialize active tab from URL query parameter
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');
                const validTabs = ['main-categories', 'sub-categories', 'places'];

                if (tab && validTabs.includes(tab)) {
                    this.activeTab = tab;
                }

                this.filterSubCategories();

                this.$watch('activeTab', (value) => {
                    // Update URL when tab changes
                    const url = new URL(window.location);
                    url.searchParams.set('tab', value);
                    window.history.pushState({}, '', url);

                    if (value === 'places') {
                        // Use a slight delay to ensure x-show transition has started/finished rendering
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (this.map) {
                                    this.map.invalidateSize();
                                }
                            }, 200);
                        });
                    }
                });
            },

            // Main Category Methods
            resetMainCatForm() {
                this.mainCatEditMode = false;
                this.mainCatFormAction = "{{ route('admin.featured-places.main-categories.store') }}";
                this.mainCatData = { id: null, name_ar: '', name_en: '', icon_name: '', is_active: true };
            },
            editMainCategory(cat) {
                this.activeTab = 'main-categories';
                this.mainCatEditMode = true;
                this.mainCatFormAction = "{{ route('admin.featured-places.main-categories.update', '0') }}".replace('/0', '/' + cat.id);
                this.mainCatData = {
                    id: cat.id,
                    name_ar: cat.name_ar,
                    name_en: cat.name_en,
                    icon_name: cat.icon_name,
                    is_active: cat.is_active
                };
            },

            // Sub Category Methods
            resetSubCatForm() {
                this.subCatEditMode = false;
                this.subCatFormAction = "{{ route('admin.featured-places.sub-categories.store') }}";
                this.subCatData = { id: null, main_category_id: '', name_ar: '', name_en: '', is_active: true };
            },
            editSubCategory(sub) {
                this.activeTab = 'sub-categories';
                this.subCatEditMode = true;
                this.subCatFormAction = "{{ route('admin.featured-places.sub-categories.update', '0') }}".replace('/0', '/' + sub.id);
                this.subCatData = {
                    id: sub.id,
                    main_category_id: sub.main_category_id,
                    name_ar: sub.name_ar,
                    name_en: sub.name_en,
                    is_active: sub.is_active
                };
            },

            // Featured Place Methods
            filterSubCategories() {
                if (!this.selectedMainCategory) {
                    this.filteredSubCategories = [];
                    return;
                }
                this.filteredSubCategories = this.allSubCategories.filter(sub => sub.main_category_id == this.selectedMainCategory);
                // Don't reset sub selection here if in edit mode and valid
            },
            resetPlaceForm() {
                this.placeEditMode = false;
                this.placeFormAction = "{{ route('admin.featured-places.places.store') }}";
                this.selectedMainCategory = '';
                this.filterSubCategories();
                this.placeData = {
                    id: null,
                    sub_category_id: '',
                    name_ar: '',
                    name_en: '',
                    is_active: true
                };

                // Reset location dropdowns
                document.getElementById('fp_country_id').value = '';
                // Trigger change to reset dependent dropdowns
                document.getElementById('fp_country_id').dispatchEvent(new Event('change'));

                if(this.map) {
                    this.map.invalidateSize();
                    // Reset to default Cairo view
                     this.map.setView([30.0444, 31.2357], 6);
                     // Clear drawings
                     if(this.map.drawnItems) this.map.drawnItems.clearLayers();
                     if(this.map.updateBoundaryInput) this.map.updateBoundaryInput();
                }
            },
            async editPlace(place) {
                this.activeTab = 'places';
                this.placeEditMode = true;
                this.placeFormAction = "{{ route('admin.featured-places.places.update', '0') }}".replace('/0', '/' + place.id);

                this.selectedMainCategory = place.main_category_id;
                this.filterSubCategories();

                this.placeData = {
                    id: place.id,
                    sub_category_id: place.sub_category_id,
                    name_ar: place.name_ar,
                    name_en: place.name_en,
                    is_active: place.is_active
                };

                // Populate Locations
                const countryId = place.country_id;
                const regionId = place.region_id;
                const cityId = place.city_id;
                const districtId = place.district_id;

                // Set Country
                const countrySelect = document.getElementById('fp_country_id');
                countrySelect.value = countryId;

                // Trigger Country Change and wait
                await window.loadRegions(countryId);
                document.getElementById('fp_region_id').value = regionId;

                // Trigger Region Change and wait
                await window.loadCities(regionId);
                document.getElementById('fp_city_id').value = cityId;

                // Trigger City Change and wait
                await window.loadDistricts(cityId);
                if (districtId) {
                    document.getElementById('fp_district_id').value = districtId;
                }

                // Update Map
                // Ensure map is visible before calling map methods
                this.$nextTick(() => {
                    setTimeout(() => {
                        const mapInstance = this.map;
                        if(mapInstance) {
                            // Important: Resize first
                            mapInstance.invalidateSize();

                            if (place.point_lat && place.point_lng) {
                                const lat = parseFloat(place.point_lat);
                                const lng = parseFloat(place.point_lng);

                                // Move view
                                mapInstance.setView([lat, lng], 15);

                                // Update Marker
                                if (mapInstance.updateMarker) {
                                    mapInstance.updateMarker(lat, lng);
                                }

                                // Remove existing layers if any (clearing drawn items)
                                if (mapInstance.drawnItems) {
                                    mapInstance.drawnItems.clearLayers();
                                }

                                // Add Polygon if exists
                                if (place.polygon_geojson && mapInstance.drawnItems) {
                                     // Check if it's a string or object
                                     let geoJson = place.polygon_geojson;
                                     if (typeof geoJson === 'string') {
                                         try { geoJson = JSON.parse(geoJson); } catch(e) {}
                                     }

                                     if (geoJson) {
                                         const layer = L.geoJSON(geoJson);
                                         layer.eachLayer(function(l) {
                                             mapInstance.drawnItems.addLayer(l);
                                         });
                                     }
                                }

                                // Sync boundary input
                                if (mapInstance.updateBoundaryInput) {
                                    mapInstance.updateBoundaryInput();
                                }
                            }
                        }
                    }, 300);
                });
            }
        }));
    });

    // Plain JS for Location Cascading (Refactored to be accessible)
    document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('fp_country_id');
        const regionSelect = document.getElementById('fp_region_id');
        const citySelect = document.getElementById('fp_city_id');
        const districtSelect = document.getElementById('fp_district_id');

        // Helper to fetch data
        async function fetchLocations(url) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Network response was not ok');
                return await response.json();
            } catch (error) {
                console.error('Error fetching locations:', error);
                return [];
            }
        }

        function populateSelect(selectElement, items, placeholder) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
            if (Array.isArray(items)) {
                const isAr = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = isAr
                        ? (item.name_local || item.name_ar || item.name_en || item.name)
                        : (item.name_en || item.name_local || item.name);
                    if(item.lat && item.lng) {
                         option.setAttribute('data-lat', item.lat);
                         option.setAttribute('data-lng', item.lng);
                    }
                    selectElement.appendChild(option);
                });
                selectElement.disabled = false;
            }
        }

        // Expose loaders to window for Edit Mode
        window.loadRegions = async function(countryId) {
             const response = await fetchLocations(`/admin/locations/regions/${countryId}`);
             populateSelect(regionSelect, response.regions || [], "@lang('admin.select_region')");
             citySelect.innerHTML = '<option value="">@lang('admin.select_city')</option>'; citySelect.disabled = true;
             districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true;
        }

        window.loadCities = async function(regionId) {
             const response = await fetchLocations(`/admin/locations/cities/${regionId}`);
             populateSelect(citySelect, response.cities || [], "@lang('admin.select_city')");
             districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true;
        }

        window.loadDistricts = async function(cityId) {
             const response = await fetchLocations(`/admin/locations/districts/${cityId}`);
             populateSelect(districtSelect, response.districts || [], "@lang('admin.select_district')");
        }

        let currentReferenceLayer = null;

        function updateMapBoundary(level, id) {
            const mapInstance = window['map_featured-places-map'];
            if (!mapInstance || !id) return;

            // Remove previous reference layer if exists
            if (currentReferenceLayer) {
                mapInstance.removeLayer(currentReferenceLayer);
                currentReferenceLayer = null;
            }

            // Fetch polygon for the selected location
            fetch(`{{ url('admin/location-polygons') }}?level=${level}&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    // Response structure: { regions: [ ... ], ... }
                    const key = level === 'region' ? 'regions' : (level === 'city' ? 'cities' : 'districts');
                    if (data[key] && data[key].length > 0) {
                        const item = data[key][0];

                        // Priority 1: If polygon exists, draw it and fit bounds
                        if (item.polygon) {
                            currentReferenceLayer = L.geoJSON(item.polygon, {
                                style: {
                                    color: '#3388ff',
                                    weight: 2,
                                    opacity: 0.6,
                                    fillOpacity: 0.1,
                                    dashArray: '5, 5' // Dashed line to indicate reference
                                },
                                interactive: false // Don't block clicks
                            }).addTo(mapInstance);

                            mapInstance.fitBounds(currentReferenceLayer.getBounds());
                        }
                        // Priority 2: If no polygon but lat/lng exists, fly to point
                        else if (item.lat != null && item.lng != null) {
                            const zoom = level === 'region' ? 9 : (level === 'city' ? 11 : 13);
                            mapInstance.flyTo([item.lat, item.lng], zoom);
                        }
                    }
                })
                .catch(err => console.error("Error fetching location polygon:", err));
        }

        // Map Interaction Hook
        function flyToSelected(selectElement, level) {
             const selectedOption = selectElement.options[selectElement.selectedIndex];
             const lat = selectedOption.getAttribute('data-lat');
             const lng = selectedOption.getAttribute('data-lng');
             const mapInstance = window['map_featured-places-map'];
             const id = selectElement.value;

             // Always try to fetch and show boundary first
             if (id && level) {
                 updateMapBoundary(level, id);
             }
             // Fallback to simple flyTo if logic requires (though updateMapBoundary handles this too)
             else if (lat && lng && mapInstance) {
                 mapInstance.flyTo([lat, lng], 12);
             }
        }

        // Event Listeners
        countrySelect.addEventListener('change', async function() {
            if(this.value) {
                await window.loadRegions(this.value);
                flyToSelected(this, null); // Country has no boundary API usually here, or handled differently
            } else {
                regionSelect.innerHTML = '<option value="">@lang('admin.select_region')</option>'; regionSelect.disabled = true;
                citySelect.innerHTML = '<option value="">@lang('admin.select_city')</option>'; citySelect.disabled = true;
                districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true;
            }
        });

        regionSelect.addEventListener('change', async function() {
            if(this.value) {
                await window.loadCities(this.value);
                flyToSelected(this, 'region');
            } else {
                citySelect.innerHTML = '<option value="">@lang('admin.select_city')</option>'; citySelect.disabled = true;
                districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true;
            }
        });

        citySelect.addEventListener('change', async function() {
            if(this.value) {
                await window.loadDistricts(this.value);
                flyToSelected(this, 'city');
            } else {
                districtSelect.innerHTML = '<option value="">@lang('admin.select_district')</option>'; districtSelect.disabled = true;
            }
        });

        districtSelect.addEventListener('change', function() {
             flyToSelected(this, 'district');
        });
    });
</script>
@endpush
@endsection
