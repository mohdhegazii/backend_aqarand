@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' Property Model')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.property-models.store') }}">
            @csrf

            <!-- Common Fields -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">@lang('admin.details')</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project *</label>
                        <select name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Type *</label>
                        <select name="unit_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Select Unit Type</option>
                            @foreach($unitTypes as $type)
                                <option value="{{ $type->id }}" {{ old('unit_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_active" class="ml-2 inline text-sm text-gray-900">Active</label>
                    </div>
                </div>
            </div>

            <!-- Specs & Areas -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Specs & Areas</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bedrooms</label>
                        <input type="number" name="bedrooms" value="{{ old('bedrooms') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bathrooms</label>
                        <input type="number" name="bathrooms" value="{{ old('bathrooms') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min BUA</label>
                        <input type="number" step="0.01" name="min_bua" value="{{ old('min_bua') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Max BUA</label>
                        <input type="number" step="0.01" name="max_bua" value="{{ old('max_bua') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min Land Area</label>
                        <input type="number" step="0.01" name="min_land_area" value="{{ old('min_land_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Max Land Area</label>
                        <input type="number" step="0.01" name="max_land_area" value="{{ old('max_land_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min Price</label>
                        <input type="number" step="0.01" name="min_price" value="{{ old('min_price') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Max Price</label>
                        <input type="number" step="0.01" name="max_price" value="{{ old('max_price') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Media</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Floorplan 2D URL</label>
                        <input type="text" name="floorplan_2d_url" value="{{ old('floorplan_2d_url') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Floorplan 3D URL</label>
                        <input type="text" name="floorplan_3d_url" value="{{ old('floorplan_3d_url') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div x-data="{ tab: 'en' }">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 rtl:space-x-reverse" aria-label="Tabs">
                        <button type="button" @click="tab = 'en'" :class="{ 'border-blue-500 text-blue-600': tab === 'en', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'en' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            English
                        </button>
                        <button type="button" @click="tab = 'ar'" :class="{ 'border-blue-500 text-blue-600': tab === 'ar', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'ar' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Arabic
                        </button>
                    </nav>
                </div>

                <!-- English Tab -->
                <div x-show="tab === 'en'" class="py-4">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name (EN) *</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (EN)</label>
                            <textarea name="description_en" class="wysiwyg">{{ old('description_en') }}</textarea>
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (English)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug (EN)</label>
                                    <input type="text" name="seo_slug_en" value="{{ old('seo_slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meta Title (EN)</label>
                                    <input type="text" name="meta_title_en" value="{{ old('meta_title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Meta Description (EN)</label>
                                    <textarea name="meta_description_en" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('meta_description_en') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arabic Tab -->
                <div x-show="tab === 'ar'" class="py-4" style="display: none;">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name (AR)</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (AR)</label>
                            <textarea name="description_ar" class="wysiwyg">{{ old('description_ar') }}</textarea>
                        </div>

                         <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (Arabic)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug (AR)</label>
                                    <input type="text" name="seo_slug_ar" value="{{ old('seo_slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meta Title (AR)</label>
                                    <input type="text" name="meta_title_ar" value="{{ old('meta_title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Meta Description (AR)</label>
                                    <textarea name="meta_description_ar" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('meta_description_ar') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('admin.property-models.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
