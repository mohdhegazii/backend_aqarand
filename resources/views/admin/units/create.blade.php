@extends('admin.layouts.app')

@section('header', __('admin.create_new') . ' Unit')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.units.store') }}">
            @csrf

            <!-- Common Fields -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">@lang('admin.details')</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project *</label>
                        <select name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Property Model</label>
                        <select name="property_model_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                             <option value="">Select Model</option>
                             {{-- Dynamic loading ideally, but showing all for now --}}
                             @foreach($propertyModels as $model)
                                 <option value="{{ $model->id }}" {{ old('property_model_id') == $model->id ? 'selected' : '' }}>{{ $model->name_en }}</option>
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
                        <label class="block text-sm font-medium text-gray-700">Status *</label>
                        <select name="unit_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="available" {{ old('unit_status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="reserved" {{ old('unit_status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="sold" {{ old('unit_status') == 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="rented" {{ old('unit_status') == 'rented' ? 'selected' : '' }}>Rented</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Construction Status</label>
                        <select name="construction_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="new_launch" {{ old('construction_status') == 'new_launch' ? 'selected' : '' }}>New Launch</option>
                            <option value="off_plan" {{ old('construction_status') == 'off_plan' ? 'selected' : '' }}>Off Plan</option>
                            <option value="under_construction" {{ old('construction_status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            <option value="ready_to_move" {{ old('construction_status') == 'ready_to_move' ? 'selected' : '' }}>Ready to Move</option>
                            <option value="livable" {{ old('construction_status') == 'livable' ? 'selected' : '' }}>Livable</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Number</label>
                        <input type="text" name="unit_number" value="{{ old('unit_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Floor Label</label>
                        <input type="text" name="floor_label" value="{{ old('floor_label') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                        <label class="block text-sm font-medium text-gray-700">BUA</label>
                        <input type="number" step="0.01" name="built_up_area" value="{{ old('built_up_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Land Area</label>
                        <input type="number" step="0.01" name="land_area" value="{{ old('land_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Garden Area</label>
                        <input type="number" step="0.01" name="garden_area" value="{{ old('garden_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Outdoor Area</label>
                        <input type="number" step="0.01" name="outdoor_area" value="{{ old('outdoor_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Roof Area</label>
                        <input type="number" step="0.01" name="roof_area" value="{{ old('roof_area') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="mb-8 border-b pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price *</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency *</label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', 'EGP') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Type</label>
                        <select name="payment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="installments" {{ old('payment_type') == 'installments' ? 'selected' : '' }}>Installments</option>
                            <option value="both" {{ old('payment_type') == 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Summary</label>
                        <textarea name="payment_summary" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('payment_summary') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mb-4 flex gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_corner" id="is_corner" value="1" {{ old('is_corner') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_corner" class="ml-2 inline text-sm text-gray-900">Is Corner</label>
                </div>
                 <div class="flex items-center">
                    <input type="checkbox" name="is_furnished" id="is_furnished" value="1" {{ old('is_furnished') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_furnished" class="ml-2 inline text-sm text-gray-900">Is Furnished</label>
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
                            <label class="block text-sm font-medium text-gray-700">Title (EN)</label>
                            <input type="text" name="title_en" value="{{ old('title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (English)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <label class="block text-sm font-medium text-gray-700">Title (AR)</label>
                            <input type="text" name="title_ar" value="{{ old('title_ar') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                         <div class="border-t pt-4 mt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">SEO (Arabic)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <a href="{{ route('admin.units.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
