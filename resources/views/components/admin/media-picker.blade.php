@props(['name', 'label' => 'Media', 'value' => null, 'multiple' => false, 'acceptedFileTypes' => 'image'])

<div x-data="{
    multiple: {{ $multiple ? 'true' : 'false' }},
    mediaId: '{{ $multiple ? '' : ($value->id ?? $value) }}',
    galleryMediaIds: @json($multiple && $value ? (is_object($value) && method_exists($value, 'pluck') ? $value->pluck('id') : collect($value)->pluck('id')) : []),
    previews: [],

    init() {
        if (this.multiple) {
            // Ensure galleryMediaIds is always an array
            if (!Array.isArray(this.galleryMediaIds)) {
                this.galleryMediaIds = [];
            }
            if (this.galleryMediaIds.length > 0) {
                this.fetchMediaPreview(this.galleryMediaIds);
            }
        } else {
            if (this.mediaId) {
                this.fetchMediaPreview([this.mediaId]);
            }
        }

        // Listen for selection from the modal
        window.addEventListener('media-selected', (e) => {
            if (e.detail.inputName === '{{ $name }}') {
                if (this.multiple) {
                    // Handle array of media items (new behavior) or single item (legacy)
                    const items = Array.isArray(e.detail.media) ? e.detail.media : [e.detail.media];

                    items.forEach(mediaItem => {
                        const id = mediaItem.id;
                        if (!this.galleryMediaIds.includes(id)) {
                            this.galleryMediaIds.push(id);
                            this.addPreview(mediaItem);
                        }
                    });
                } else {
                    // Single mode
                    const mediaItem = Array.isArray(e.detail.media) ? e.detail.media[0] : e.detail.media;
                    if (mediaItem) {
                        this.mediaId = mediaItem.id;
                        this.previews = []; // Clear previous
                        this.addPreview(mediaItem);
                    }
                }
            }
        });
    },

    async fetchMediaPreview(ids) {
        if (!Array.isArray(ids) || ids.length === 0) {
             // Safe guard against invalid calls
             return;
        }

        try {
            // Join IDs for the API call
            const idsParam = ids.join(',');
            const response = await fetch(`{{ localized_route('admin.media.index') }}?ids=${idsParam}`, {
                 headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const mediaItems = await response.json();

                // Map the results to our preview structure
                // Use a temporary map to preserve order if needed, but here we just render them
                this.previews = mediaItems.map(item => this.mapMediaToPreview(item));
            }
        } catch (e) {
            console.error('Error fetching media preview', e);
        }
    },

    addPreview(media) {
        // Optimistically add to preview list without re-fetching
        const preview = this.mapMediaToPreview(media);
        this.previews.push(preview);
    },

    mapMediaToPreview(media) {
        return {
            id: media.id,
            url: media.variants?.thumb?.url || media.variants?.thumb || media.url,
            name: media.original_name
        };
    },

    removeMedia(id) {
        if (this.multiple) {
            this.galleryMediaIds = this.galleryMediaIds.filter(item => item !== id);
            this.previews = this.previews.filter(item => item.id !== id);
        } else {
            this.mediaId = '';
            this.previews = [];
        }
    }
}" class="mb-4">

    <label class="block font-medium text-sm text-gray-700 mb-2">{{ $label }}</label>

    {{-- Hidden Inputs --}}
    <template x-if="multiple">
         <div>
            {{-- Standard array inputs for Laravel validation --}}
            <template x-for="id in galleryMediaIds" :key="id">
                 <input type="hidden" name="{{ $name }}[]" :value="id">
            </template>
         </div>
    </template>
    <template x-if="!multiple">
        <input type="hidden" name="{{ $name }}" x-model="mediaId">
    </template>

    {{-- Preview / Selection Area --}}
    <div class="space-y-4">

        {{-- List of Previews (Grid) --}}
        <div x-show="multiple && previews.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <template x-for="item in previews" :key="item.id">
                <div class="relative group border border-gray-200 rounded-lg overflow-hidden aspect-square">
                    <img :src="item.url" class="w-full h-full object-cover">

                    <button type="button"
                            @click="removeMedia(item.id)"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-sm"
                            title="{{ __('Remove') }}">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white text-[10px] p-1 truncate">
                        <span x-text="item.name"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Single Preview --}}
        <div x-show="!multiple && previews.length > 0" class="flex items-center space-x-4">
             <template x-for="item in previews" :key="item.id">
                <div class="relative w-24 h-24 border border-gray-300 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden group">
                    <img :src="item.url" class="w-full h-full object-cover">

                    <button type="button"
                            @click="removeMedia(item.id)"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                            title="{{ __('Remove') }}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
             </template>
             <div x-show="previews.length > 0">
                 <p class="text-sm text-gray-600 mb-2 truncate" x-text="previews[0].name"></p>
             </div>
        </div>

        {{-- Empty State / Button --}}
        <div class="flex items-center">
             <x-admin.media-manager-modal
                 inputName="{{ $name }}"
                 allowedType="{{ $acceptedFileTypes }}"
                 :multiple="$multiple"
                 label="{{ $value ? __('Change Media') : __('Choose Media') }}">

                <button type="button" @click="openMediaModal" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-images me-2"></i>
                    <span x-text="multiple ? '{{ __('Manage Media') }}' : (mediaId ? '{{ __('Change Media') }}' : '{{ __('Choose Media') }}')"></span>
                </button>
            </x-admin.media-manager-modal>
        </div>
    </div>
</div>
