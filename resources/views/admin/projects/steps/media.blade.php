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
            <div x-data="{
                galleryMediaIds: [],
                galleryMeta: {},
                featuredMediaId: {{ $featuredMediaId ?? 'null' }},
                previews: [],

                init() {
                    const initialMedia = @json($initialGalleryMedia);

                    if (Array.isArray(initialMedia)) {
                        initialMedia.forEach(item => {
                            this.addPreview(item);
                        });
                    }

                    // Listen for selection from the modal
                    window.addEventListener('media-selected', (e) => {
                        if (e.detail.inputName === 'gallery_media_ids') {
                            const items = Array.isArray(e.detail.media) ? e.detail.media : [e.detail.media];
                            items.forEach(mediaItem => {
                                if (!this.galleryMediaIds.includes(mediaItem.id)) {
                                    this.addPreview(mediaItem);
                                }
                            });
                        }
                    });
                },

                addPreview(media) {
                    this.galleryMediaIds.push(media.id);
                    this.previews.push({
                        id: media.id,
                        url: media.variants?.thumb?.url || media.variants?.thumb || media.url,
                        name: media.original_name || media.original_filename
                    });

                    // Initialize Meta
                    if (!this.galleryMeta[media.id]) {
                        this.galleryMeta[media.id] = {
                            alt_text: media.alt_text || '',
                            description: media.caption || media.description || ''
                        };
                    }
                },

                removeMedia(id) {
                    this.galleryMediaIds = this.galleryMediaIds.filter(item => item !== id);
                    this.previews = this.previews.filter(item => item.id !== id);
                    delete this.galleryMeta[id];
                    if (this.featuredMediaId === id) {
                        this.featuredMediaId = null;
                    }
                },

                setFeatured(id) {
                    this.featuredMediaId = id;
                }
            }" class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.gallery') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('admin.gallery_desc') ?? 'Upload multiple images for the project gallery. You can reorder them.' }}</p>

                {{-- Hidden Inputs --}}
                <template x-for="id in galleryMediaIds" :key="id">
                    <input type="hidden" name="gallery_media_ids[]" :value="id">
                </template>
                <template x-for="id in galleryMediaIds" :key="'meta-'+id">
                    <div>
                        <input type="hidden" :name="'gallery_alt_texts['+id+']'" :value="galleryMeta[id]?.alt_text || ''">
                        <input type="hidden" :name="'gallery_descriptions['+id+']'" :value="galleryMeta[id]?.description || ''">
                    </div>
                </template>
                <input type="hidden" name="featured_media_id" :value="featuredMediaId ?? ''">

                {{-- Gallery List (Responsive) --}}
                <div class="space-y-4 mb-4">
                    <template x-for="(item, index) in previews" :key="item.id">
                        <div class="flex flex-col md:flex-row gap-4 p-4 border rounded-lg bg-white shadow-sm transition-all duration-200"
                             :class="featuredMediaId === item.id ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50' : 'border-gray-200'">

                            {{-- Image Preview & Actions --}}
                            <div class="flex-shrink-0 w-full md:w-48 flex flex-col gap-2">
                                <div class="relative w-full aspect-video md:aspect-square rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                                    <img :src="item.url" class="w-full h-full object-cover">

                                    {{-- Badge --}}
                                    <div x-show="featuredMediaId === item.id"
                                         class="absolute top-2 left-2 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm z-10">
                                        FEATURED
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-1 gap-2">
                                    <button type="button"
                                            class="text-xs px-2 py-1.5 rounded border font-medium transition-colors text-center w-full shadow-sm"
                                            :class="featuredMediaId === item.id ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                            @click="setFeatured(item.id)">
                                        <i class="bi bi-star-fill me-1" x-show="featuredMediaId === item.id"></i>
                                        <span x-text="featuredMediaId === item.id ? '{{ __('Featured') }}' : '{{ __('Set as Featured') }}'"></span>
                                    </button>

                                    <button type="button"
                                            @click="removeMedia(item.id)"
                                            class="text-xs px-2 py-1.5 rounded border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 font-medium text-center w-full shadow-sm flex justify-center items-center">
                                        <i class="bi bi-trash me-1"></i> {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Metadata Inputs --}}
                            <div class="flex-grow">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 h-full">
                                    <div class="flex flex-col">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('Alt Text') }} <span class="text-red-600">*</span>
                                        </label>
                                        <input class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2"
                                               type="text"
                                               x-model="galleryMeta[item.id].alt_text"
                                               placeholder="Image description for SEO"
                                               required />
                                        <p class="text-[10px] text-gray-400 mt-1">Required for SEO accessiblity.</p>
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('Description') }}
                                        </label>
                                        <textarea class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 flex-grow"
                                               rows="3"
                                               x-model="galleryMeta[item.id].description"
                                               placeholder="Caption displayed to users"></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>

                    <div x-show="previews.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <p class="text-gray-500 text-sm">{{ __('No images added yet.') }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                     <x-admin.media-manager-modal
                         inputName="gallery_media_ids"
                         allowedType="image"
                         :multiple="true"
                         label="{{ __('Add Images') }}">
                        <button type="button" @click="openMediaModal" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="bi bi-images me-2"></i>
                            {{ __('Add Images') }}
                        </button>
                    </x-admin.media-manager-modal>

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
