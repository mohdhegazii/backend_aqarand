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
                    @endphp
                    <select name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ $isLockedProject ? 'disabled' : '' }}>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $selectedProject == $project->id ? 'selected' : '' }}>{{ $project->name_en }}</option>
                        @endforeach
                    </select>
                    @if($isLockedProject)
                        <input type="hidden" name="project_id" value="{{ $selectedProject }}">
                    @endif
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
