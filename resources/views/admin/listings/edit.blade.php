@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' Listing')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route($adminRoutePrefix.'listings.update', $listing) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Unit -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Unit *</label>
                    <x-lookup.select
                        name="unit_id"
                        url="/admin/lookups/units"
                        placeholder="{{ __('admin.select_unit') }}"
                        :selected-id="old('unit_id', $listing->unit_id)"
                        :selected-text="$listing->unit ? ($listing->unit->unit_number . ' - ' . ($listing->unit->project->name_en ?? '')) : ''"
                    />
                </div>

                <!-- Type & Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Listing Type *</label>
                    <select name="listing_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="primary" {{ old('listing_type', $listing->listing_type) == 'primary' ? 'selected' : '' }}>Primary</option>
                        <option value="resale" {{ old('listing_type', $listing->listing_type) == 'resale' ? 'selected' : '' }}>Resale</option>
                        <option value="rental" {{ old('listing_type', $listing->listing_type) == 'rental' ? 'selected' : '' }}>Rental</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status *</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="draft" {{ old('status', $listing->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $listing->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="hidden" {{ old('status', $listing->status) == 'hidden' ? 'selected' : '' }}>Hidden</option>
                        <option value="sold" {{ old('status', $listing->status) == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="rented" {{ old('status', $listing->status) == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="expired" {{ old('status', $listing->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title (EN) *</label>
                    <input type="text" name="title_en" value="{{ old('title_en', $listing->title_en) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title (AR)</label>
                    <input type="text" name="title_ar" value="{{ old('title_ar', $listing->title_ar) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug (EN)</label>
                    <input type="text" name="slug_en" value="{{ old('slug_en', $listing->slug_en) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug (AR)</label>
                    <input type="text" name="slug_ar" value="{{ old('slug_ar', $listing->slug_ar) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- SEO -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Title (EN)</label>
                    <input type="text" name="seo_title_en" value="{{ old('seo_title_en', $listing->seo_title_en) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Title (AR)</label>
                    <input type="text" name="seo_title_ar" value="{{ old('seo_title_ar', $listing->seo_title_ar) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Description (EN)</label>
                    <textarea name="seo_description_en" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('seo_description_en', $listing->seo_description_en) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Description (AR)</label>
                    <textarea name="seo_description_ar" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('seo_description_ar', $listing->seo_description_ar) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $listing->is_featured) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_featured" class="ml-2 inline text-sm text-gray-900">Featured</label>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route($adminRoutePrefix.'listings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
