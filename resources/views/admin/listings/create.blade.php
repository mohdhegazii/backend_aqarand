@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' Listing')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.listings.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Unit -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Unit *</label>
                    <select name="unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_number }} - {{ $unit->project->name_en ?? 'No Project' }} ({{ number_format($unit->price) }} {{ $unit->currency_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type & Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Listing Type *</label>
                    <select name="listing_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="primary" {{ old('listing_type') == 'primary' ? 'selected' : '' }}>Primary</option>
                        <option value="resale" {{ old('listing_type') == 'resale' ? 'selected' : '' }}>Resale</option>
                        <option value="rental" {{ old('listing_type') == 'rental' ? 'selected' : '' }}>Rental</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status *</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Hidden</option>
                        <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="rented" {{ old('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title (EN) *</label>
                    <input type="text" name="title_en" value="{{ old('title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title (AR)</label>
                    <input type="text" name="title_ar" value="{{ old('title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug (EN)</label>
                    <input type="text" name="slug_en" value="{{ old('slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug (AR)</label>
                    <input type="text" name="slug_ar" value="{{ old('slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- SEO -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Title (EN)</label>
                    <input type="text" name="seo_title_en" value="{{ old('seo_title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Title (AR)</label>
                    <input type="text" name="seo_title_ar" value="{{ old('seo_title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Description (EN)</label>
                    <textarea name="seo_description_en" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('seo_description_en') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SEO Description (AR)</label>
                    <textarea name="seo_description_ar" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('seo_description_ar') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_featured" class="ml-2 inline text-sm text-gray-900">Featured</label>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.listings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
