@extends('admin.layouts.app')

@section('header')
    {{ $project->exists ? __('admin.edit_project') . ': ' . ($project->name_ar ?? $project->name_en) : __('admin.create_new') }}
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <!-- Wizard Progress -->
    @if(view()->exists('admin.projects.partials.wizard_steps'))
        @include('admin.projects.partials.wizard_steps', ['currentStep' => 2, 'projectId' => $project->id])
    @else
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Debug Error!</strong>
            <span class="block sm:inline">The view 'admin.projects.partials.wizard_steps' was not found.</span>
            <div class="mt-2 text-xs font-mono bg-white p-2 rounded border border-red-200">
                <strong>Current View Paths:</strong>
                <pre>{{ print_r(config('view.paths'), true) }}</pre>
            </div>
            <div class="mt-2 text-xs font-mono bg-white p-2 rounded border border-red-200">
                <strong>Exists check failed for:</strong> admin.projects.partials.wizard_steps
            </div>
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ __('admin.projects.amenities') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.projects.steps.amenities') }}</p>
    </div>

    <form action="{{ route('admin.projects.steps.amenities.store', $project->id) }}" method="POST" id="amenities-form">
        @csrf

        <div class="space-y-6">
            @if(isset($amenitiesByCategory) && $amenitiesByCategory->count() > 0)
                {{-- Iterate over the Collection of AmenityCategory objects --}}
                @foreach($amenitiesByCategory as $category)
                    <div class="border rounded-md p-4 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                            @if(!empty($category->image_path))
                                <img src="{{ asset('storage/' . $category->image_path) }}" alt="" class="h-6 w-6 mr-2 object-cover rounded-full">
                            @endif
                            {{ $category->display_name }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($category->amenities as $amenity)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="amenity-{{ $amenity->id }}"
                                            name="amenities[]"
                                            value="{{ $amenity->id }}"
                                            type="checkbox"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-600 dark:border-gray-500 dark:focus:ring-indigo-600"
                                            {{ in_array($amenity->id, $selectedAmenityIds) ? 'checked' : '' }}
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="amenity-{{ $amenity->id }}" class="font-medium text-gray-700 dark:text-gray-300 select-none cursor-pointer flex items-center">
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
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    {{ __('admin.no_amenities_found') ?? 'No amenities found. Please add amenities first.' }}
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-8 flex justify-between">
            <a
                href="{{ route('admin.projects.steps.basics', $project->id) }}"
                class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
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
