@extends('admin.layouts.app')

@section('header')
    <h2 class="text-xl font-bold text-gray-800">{{ __('admin.projects.media') }}</h2>
    <p class="text-sm text-gray-500">{{ __('admin.projects.steps.media_desc') ?? 'Manage project gallery and brochure' }}</p>
@endsection

@section('content')
    @if(view()->exists('admin.projects.partials.wizard_steps'))
        @include('admin.projects.partials.wizard_steps', ['currentStep' => 4, 'projectId' => $project->id])
    @else
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">The view 'admin.projects.partials.wizard_steps' was not found.</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.projects.steps.media.store', $project->id) }}" method="POST" id="media-form">
            @csrf

            {{-- Gallery --}}
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.gallery') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('admin.gallery_desc') ?? 'Upload multiple images for the project gallery. You can reorder them.' }}</p>

                <x-admin.media-picker
                    name="gallery_media_ids"
                    label=""
                    :value="$initialGalleryMedia"
                    :multiple="true"
                    :featuredId="$featuredMediaId"
                    accepted-file-types="image"
                />

                <div class="flex items-center justify-between mt-2">
                     <p class="text-xs text-gray-400">
                        {{ __('admin.supported_formats') }}: JPG, PNG, WEBP.
                    </p>
                </div>
            </div>

            <hr class="my-8 border-gray-200">

            {{-- Brochure --}}
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.brochure') }}</h3>

                <div class="max-w-xl">
                    <x-admin.media-picker
                        name="brochure_media_id"
                        :value="$brochureMediaFile"
                        :multiple="false"
                        accepted-file-types="application/pdf"
                    />
                     <p class="mt-2 text-xs text-gray-400">
                        {{ __('admin.supported_formats') }}: PDF {{ __('admin.only') }}.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-between items-center">
                <a href="{{ route('admin.projects.steps.amenities', $project->id) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('admin.previous') }}
                </a>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('admin.save_and_next') }}
                </button>
            </div>
        </form>
    </div>
@endsection
