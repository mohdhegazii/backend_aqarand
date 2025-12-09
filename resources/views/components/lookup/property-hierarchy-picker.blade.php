<div
    x-data="{
        categories: {{ json_encode($categories) }},
        selectedCategoryId: '{{ $selectedCategoryId }}',
        selectedPropertyTypeId: '{{ $selectedPropertyTypeId }}',
        selectedUnitTypeId: '{{ $selectedUnitTypeId }}',

        propertyTypes: {{ json_encode($propertyTypes) }},
        unitTypes: {{ json_encode($unitTypes) }},

        isLoadingPropertyTypes: false,
        isLoadingUnitTypes: false,

        async onCategoryChange() {
            this.selectedPropertyTypeId = '';
            this.selectedUnitTypeId = '';
            this.propertyTypes = [];
            this.unitTypes = [];

            if (!this.selectedCategoryId) return;

            this.isLoadingPropertyTypes = true;
            try {
                const response = await fetch(`{{ route('lookups.property_types') }}?category_id=${this.selectedCategoryId}`);
                const data = await response.json();
                this.propertyTypes = data;
            } catch (error) {
                console.error('Error fetching property types:', error);
            } finally {
                this.isLoadingPropertyTypes = false;
            }
        },

        async onPropertyTypeChange() {
            this.selectedUnitTypeId = '';
            this.unitTypes = [];

            if (!this.selectedPropertyTypeId) return;

            this.isLoadingUnitTypes = true;
            try {
                const response = await fetch(`{{ route('lookups.unit_types') }}?property_type_id=${this.selectedPropertyTypeId}`);
                const data = await response.json();
                this.unitTypes = data;
            } catch (error) {
                console.error('Error fetching unit types:', error);
            } finally {
                this.isLoadingUnitTypes = false;
            }
        }
    }"
    class="grid grid-cols-1 md:grid-cols-3 gap-6"
>
    <!-- Category Select -->
    <div>
        <label for="{{ $categoryFieldName }}" class="block text-sm font-medium text-gray-700">
            {{ __('admin.lookups.category') }} {{ $required ? '*' : '' }}
        </label>
        <select
            name="{{ $categoryFieldName }}"
            id="{{ $categoryFieldName }}"
            x-model="selectedCategoryId"
            @change="onCategoryChange()"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            {{ $required ? 'required' : '' }}
        >
            <option value="">{{ __('admin.select') }}</option>
            <template x-for="category in categories" :key="category.id">
                <option :value="category.id" x-text="category.name_ar || category.name_en" :selected="category.id == selectedCategoryId"></option>
            </template>
        </select>
        @error($categoryFieldName)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Property Type Select -->
    <div>
        <label for="{{ $propertyTypeFieldName }}" class="block text-sm font-medium text-gray-700">
            {{ __('admin.lookups.property_type') }} {{ $required ? '*' : '' }}
        </label>
        <div class="relative">
            <select
                name="{{ $propertyTypeFieldName }}"
                id="{{ $propertyTypeFieldName }}"
                x-model="selectedPropertyTypeId"
                @change="onPropertyTypeChange()"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 disabled:bg-gray-100 disabled:text-gray-500"
                :disabled="!selectedCategoryId || isLoadingPropertyTypes"
                {{ $required ? 'required' : '' }}
            >
                <option value="">{{ __('admin.select') }}</option>
                <template x-for="pt in propertyTypes" :key="pt.id">
                    <option :value="pt.id" x-text="pt.name_local || pt.name_en" :selected="pt.id == selectedPropertyTypeId"></option>
                </template>
            </select>
            <div x-show="isLoadingPropertyTypes" class="absolute inset-y-0 right-0 flex items-center pr-8 pointer-events-none">
                <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        @error($propertyTypeFieldName)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Unit Type Select -->
    <div>
        <label for="{{ $unitTypeFieldName }}" class="block text-sm font-medium text-gray-700">
            {{ __('admin.lookups.unit_type') }} {{ $required ? '*' : '' }}
        </label>
        <div class="relative">
            <select
                name="{{ $unitTypeFieldName }}"
                id="{{ $unitTypeFieldName }}"
                x-model="selectedUnitTypeId"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 disabled:bg-gray-100 disabled:text-gray-500"
                :disabled="!selectedPropertyTypeId || isLoadingUnitTypes"
                {{ $required ? 'required' : '' }}
            >
                <option value="">{{ __('admin.select') }}</option>
                <template x-for="ut in unitTypes" :key="ut.id">
                    <option :value="ut.id" x-text="ut.name_local || ut.name_en" :selected="ut.id == selectedUnitTypeId"></option>
                </template>
            </select>
             <div x-show="isLoadingUnitTypes" class="absolute inset-y-0 right-0 flex items-center pr-8 pointer-events-none">
                <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        @error($unitTypeFieldName)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
