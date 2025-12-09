@extends('admin.layouts.app')

@section('header')
    {{ $project->exists ? __('admin.edit_project') . ': ' . ($project->name_ar ?? $project->name_en) : __('admin.create_new') }}
@endsection

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <!-- Step Indicator -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">{{ __('admin.amenities') }}</h2>
        <p class="text-sm text-gray-500">{{ __('admin.project_wizard.step_2_of_x') }}</p>
        <div class="mt-2 h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-indigo-600 rounded-full" style="width: 30%"></div>
        </div>
    </div>

    <form action="{{ route('admin.projects.steps.amenities.store', $project->id) }}" method="POST" id="amenities-form">
        @csrf

        <div class="space-y-6">
            @if(count($amenitiesByCategory) > 0)
                @foreach($amenitiesByCategory as $categoryId => $categoryData)
                    <div class="border rounded-md p-4 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center">
                            @if(!empty($categoryData['category']->image_path))
                                <img src="{{ asset('storage/' . $categoryData['category']->image_path) }}" alt="" class="h-6 w-6 mr-2 object-cover rounded-full">
                            @endif
                            {{ $categoryData['category']->display_name }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($categoryData['amenities'] as $amenity)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="amenity-{{ $amenity->id }}"
                                            name="amenities[]"
                                            value="{{ $amenity->id }}"
                                            type="checkbox"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            {{ in_array($amenity->id, $selectedAmenityIds) ? 'checked' : '' }}
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="amenity-{{ $amenity->id }}" class="font-medium text-gray-700 select-none cursor-pointer flex items-center">
                                            @if($amenity->icon_class)
                                                <i class="{{ $amenity->icon_class }} mr-1 text-gray-400"></i>
                                            @endif
                                            {{ $amenity->display_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8 text-gray-500">
                    {{ __('admin.no_amenities_found') ?? 'No amenities found. Please add amenities first.' }}
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-8 flex justify-between">
            <a
                href="{{ route('admin.projects.steps.basics', $project->id) }}"
                class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                {{ __('admin.previous') }}
            </a>
            <button
                type="submit"
                class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                {{ __('admin.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
