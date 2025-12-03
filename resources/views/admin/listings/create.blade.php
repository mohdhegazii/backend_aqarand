@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' Listing')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.listings.store') }}">
            @csrf

            <!-- Common Fields -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">@lang('admin.details')</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <div>
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_featured" class="ml-2 inline text-sm text-gray-900">Featured</label>
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
                            <label class="block text-sm font-medium text-gray-700">Title (EN) *</label>
                            <input type="text" name="title_en" value="{{ old('title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (English)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug (EN)</label>
                                    <input type="text" name="slug_en" value="{{ old('slug_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SEO Title (EN)</label>
                                    <input type="text" name="seo_title_en" value="{{ old('seo_title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">SEO Description (EN)</label>
                                    <textarea name="seo_description_en" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('seo_description_en') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arabic Tab -->
                <div x-show="tab === 'ar'" class="py-4" style="display: none;">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title (AR)</label>
                            <input type="text" name="title_ar" value="{{ old('title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                         <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (Arabic)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug (AR)</label>
                                    <input type="text" name="slug_ar" value="{{ old('slug_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SEO Title (AR)</label>
                                    <input type="text" name="seo_title_ar" value="{{ old('seo_title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">SEO Description (AR)</label>
                                    <textarea name="seo_description_ar" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('seo_description_ar') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('admin.listings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
