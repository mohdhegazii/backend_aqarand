@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' Property Model')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route($adminRoutePrefix.'property-models.store') }}">
            @csrf
            @if(!empty($redirectTo))
                <input type="hidden" name="redirect" value="{{ $redirectTo }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Context -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project *</label>
                    @php
                        $isLockedProject = !empty($projectId);
                        $selectedProject = old('project_id', $projectId);
                        // We need a way to pass text if it's locked, but controller might not pass project object if it was just an ID.
                        // Assuming if locked, user came from project page so context is known or we can just show ID/loading.
                        // For better UX, we might want to fetch text or just assume empty for now if not provided.
                        // But wait, if locked, the input is disabled, so search won't work anyway.
                        // We should render a hidden input and a disabled text input, OR make the component support 'disabled' prop.
                        // Let's add 'disabled' support to x-lookup.select if easy, or fallback to simple input if locked.
                    @endphp

                    @if($isLockedProject)
                        <input type="hidden" name="project_id" value="{{ $selectedProject }}">
                        <input type="text" value="{{ $projects->firstWhere('id', $selectedProject)->name_en ?? 'Project #'.$selectedProject }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 cursor-not-allowed shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                    @else
                        <x-lookup.select
                            name="project_id"
                            url="/admin/lookups/projects"
                            placeholder="{{ __('admin.select_project') }}"
                            :selected-id="$selectedProject"
                        />
                    @endif
                </div>
                <div class="col-span-1 md:col-span-2">
                    <x-lookup.property-hierarchy-picker
                        :selected-category-id="old('category_id')"
                        :selected-property-type-id="old('property_type_id')"
                        :selected-unit-type-id="old('unit_type_id')"
                    />
                </div>

                <!-- Names -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name (EN) *</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name (AR)</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Specs -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bedrooms</label>
                    <input type="number" name="bedrooms" value="{{ old('bedrooms') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bathrooms</label>
                    <input type="number" name="bathrooms" value="{{ old('bathrooms') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Areas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min BUA</label>
                    <input type="number" step="0.01" name="min_bua" value="{{ old('min_bua') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max BUA</label>
                    <input type="number" step="0.01" name="max_bua" value="{{ old('max_bua') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                 <!-- Prices -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Min Price</label>
                    <input type="number" step="0.01" name="min_price" value="{{ old('min_price') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Price</label>
                    <input type="number" step="0.01" name="max_price" value="{{ old('max_price') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Descriptions -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description (EN)</label>
                    <textarea name="description_en" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description_en') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description (AR)</label>
                    <textarea name="description_ar" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description_ar') }}</textarea>
                </div>

                <!-- SEO -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Slug (EN)</label>
                    <input type="text" name="seo_slug_en" value="{{ old('seo_slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Slug (AR)</label>
                    <input type="text" name="seo_slug_ar" value="{{ old('seo_slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="md:col-span-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_active" class="ml-2 inline text-sm text-gray-900">Active</label>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route($adminRoutePrefix.'property-models.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
