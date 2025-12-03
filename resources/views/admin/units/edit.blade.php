@extends('admin.layouts.app')

@section('header', __('admin.edit') . ' Unit')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('admin.units.update', $unit) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Context -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project *</label>
                    <select name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $unit->project_id) == $project->id ? 'selected' : '' }}>{{ $project->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Property Model</label>
                    <select name="property_model_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                         <option value="">Select Model</option>
                         @foreach($propertyModels as $model)
                             <option value="{{ $model->id }}" {{ old('property_model_id', $unit->property_model_id) == $model->id ? 'selected' : '' }}>{{ $model->name_en }}</option>
                         @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit Type *</label>
                    <select name="unit_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">Select Unit Type</option>
                        @foreach($unitTypes as $type)
                            <option value="{{ $type->id }}" {{ old('unit_type_id', $unit->unit_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Info -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit Number</label>
                    <input type="text" name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Floor Label</label>
                    <input type="text" name="floor_label" value="{{ old('floor_label', $unit->floor_label) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sales Status *</label>
                        <select name="unit_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="available" {{ old('unit_status', $unit->unit_status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="reserved" {{ old('unit_status', $unit->unit_status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="sold" {{ old('unit_status', $unit->unit_status) == 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="rented" {{ old('unit_status', $unit->unit_status) == 'rented' ? 'selected' : '' }}>Rented</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Construction Status</label>
                        <select name="construction_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="new_launch" {{ old('construction_status', $unit->construction_status) == 'new_launch' ? 'selected' : '' }}>New Launch</option>
                            <option value="off_plan" {{ old('construction_status', $unit->construction_status) == 'off_plan' ? 'selected' : '' }}>Off Plan</option>
                            <option value="under_construction" {{ old('construction_status', $unit->construction_status) == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            <option value="ready_to_move" {{ old('construction_status', $unit->construction_status) == 'ready_to_move' ? 'selected' : '' }}>Ready to Move</option>
                            <option value="livable" {{ old('construction_status', $unit->construction_status) == 'livable' ? 'selected' : '' }}>Livable</option>
                        </select>
                    </div>
                </div>

                <div>
                     <label class="block text-sm font-medium text-gray-700">Delivery Year</label>
                     <input type="number" name="delivery_year" value="{{ old('delivery_year', $unit->delivery_year) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" min="2000" max="2100">
                </div>

                 <!-- Pricing -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Price *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $unit->price) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Currency *</label>
                    <input type="text" name="currency_code" value="{{ old('currency_code', $unit->currency_code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>

                 <!-- Areas -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700">BUA</label>
                    <input type="number" step="0.01" name="built_up_area" value="{{ old('built_up_area', $unit->built_up_area) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Land Area</label>
                    <input type="number" step="0.01" name="land_area" value="{{ old('land_area', $unit->land_area) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Specs -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bedrooms</label>
                    <input type="number" name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bathrooms</label>
                    <input type="number" name="bathrooms" value="{{ old('bathrooms', $unit->bathrooms) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Title (EN)</label>
                    <input type="text" name="title_en" value="{{ old('title_en', $unit->title_en) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title (AR)</label>
                    <input type="text" name="title_ar" value="{{ old('title_ar', $unit->title_ar) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="md:col-span-2 flex gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_corner" id="is_corner" value="1" {{ old('is_corner', $unit->is_corner) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_corner" class="ml-2 inline text-sm text-gray-900">Is Corner</label>
                    </div>
                     <div class="flex items-center">
                        <input type="checkbox" name="is_furnished" id="is_furnished" value="1" {{ old('is_furnished', $unit->is_furnished) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_furnished" class="ml-2 inline text-sm text-gray-900">Is Furnished</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.units.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
