@props(['name', 'label' => 'Media', 'value' => null, 'multiple' => false, 'acceptedFileTypes' => 'image', 'featuredId' => null])

<div x-data="{
    multiple: {{ $multiple ? 'true' : 'false' }},
    mediaId: '{{ $multiple ? '' : ($value->id ?? $value) }}',
    // Initialize previews from value if it's a collection/array of objects, otherwise empty
    previews: @json($multiple && $value ? $value : []),
    // Initialize featuredId
    featuredId: @json($featuredId),
    // Map for alt texts: { id: 'alt text' }
    altMap: {},

    init() {
        // Initialize altMap from previews
        if (this.previews.length > 0) {
            this.previews.forEach(p => {
                this.altMap[p.id] = p.alt_text || '';
            });
        }

        // If multiple and no featured set, default to first
        if (this.multiple) {
            this.ensureDefaultFeatured();
        }

        // Listen for selection from the modal
        window.addEventListener('media-selected', (e) => {
            if (e.detail.inputName === '{{ $name }}') {
                if (this.multiple) {
                    const items = Array.isArray(e.detail.media) ? e.detail.media : [e.detail.media];
                    items.forEach(mediaItem => {
                        // Avoid duplicates
                        if (!this.previews.some(p => p.id === mediaItem.id)) {
                            const preview = this.mapMediaToPreview(mediaItem);
                            this.previews.push(preview);
                            // Init alt text
                            this.altMap[preview.id] = preview.alt_text || '';
                        }
                    });
                    this.ensureDefaultFeatured();
                } else {
                    // Single mode
                    const mediaItem = Array.isArray(e.detail.media) ? e.detail.media[0] : e.detail.media;
                    if (mediaItem) {
                        this.mediaId = mediaItem.id;
                        this.previews = [this.mapMediaToPreview(mediaItem)];
                        this.altMap[mediaItem.id] = mediaItem.alt_text || '';
                    }
                }
            }
        });
    },

    mapMediaToPreview(media) {
        return {
            id: media.id,
            url: media.variants?.thumb?.url || media.variants?.thumb || media.url,
            name: media.original_name,
            alt_text: media.alt_text || ''
        };
    },

    removeMedia(id) {
        if (this.multiple) {
            this.previews = this.previews.filter(item => item.id !== id);
            delete this.altMap[id];
            this.syncFeaturedAfterRemoval();
        } else {
            this.mediaId = '';
            this.previews = [];
        }
    },

    getColsForCount(n) {
        if (n <= 1) return 'grid-cols-1';
        if (n === 2) return 'grid-cols-2';
        if (n <= 4) return 'grid-cols-4';
        if (n <= 6) return 'grid-cols-6';
        return 'grid-cols-8';
    },

    ensureDefaultFeatured() {
        if (this.previews.length > 0 && (!this.featuredId || !this.previews.some(p => p.id == this.featuredId))) {
            this.featuredId = this.previews[0].id;
        }
    },

    syncFeaturedAfterRemoval() {
         if (this.previews.length > 0) {
            if (!this.featuredId || !this.previews.some(p => p.id == this.featuredId)) {
                this.featuredId = this.previews[0].id;
            }
         } else {
             this.featuredId = null;
         }
    },

    validateAlt() {
        return this.previews.every(p => (this.altMap[p.id] ?? '').trim().length > 0);
    }

}" class="mb-4">

    <label class="block font-medium text-sm text-gray-700 mb-2">{{ $label }}</label>

    {{-- Hidden Inputs --}}
    <template x-if="multiple">
         <div>
            {{-- Main Gallery IDs (JSON) --}}
            <input type="hidden" name="{{ $name }}" :value="JSON.stringify(previews.map(p => p.id))">
            {{-- Featured ID --}}
            <input type="hidden" name="featured_media_id" :value="featuredId ?? ''">
            {{-- Alt Text Map (JSON) --}}
            <input type="hidden" name="gallery_alt_map" :value="JSON.stringify(altMap)">
         </div>
    </template>

    <template x-if="!multiple">
        <input type="hidden" name="{{ $name }}" x-model="mediaId">
    </template>

    {{-- Preview / Selection Area --}}
    <div class="space-y-4">

        {{-- Multiple: Responsive Grid --}}
        <div x-show="multiple && previews.length > 0"
             class="grid gap-3 transition-all duration-300"
             :class="`grid-cols-2 sm:${getColsForCount(previews.length)} md:${getColsForCount(previews.length)} lg:${getColsForCount(previews.length)} xl:grid-cols-8`">

            <template x-for="item in previews" :key="item.id">
                <div class="relative rounded-xl overflow-hidden border bg-white transition-shadow hover:shadow-md"
                     :class="(featuredId == item.id) ? 'ring-2 ring-indigo-600 border-indigo-600' : 'border-gray-200'">

                    {{-- Image --}}
                    <div class="aspect-square w-full overflow-hidden bg-gray-100">
                        <img :src="item.url" class="w-full h-full object-cover">
                    </div>

                    {{-- Featured Badge --}}
                    <div class="absolute top-2 left-2 z-10" x-show="featuredId == item.id">
                        <span class="text-[10px] font-bold bg-indigo-600 text-white px-2 py-1 rounded shadow-sm">
                            {{ __('FEATURED') }}
                        </span>
                    </div>

                    {{-- Set Featured Button --}}
                    <button type="button"
                            x-show="featuredId != item.id"
                            class="absolute top-2 left-2 z-10 text-[10px] px-2 py-1 rounded bg-white/90 border border-gray-200 hover:bg-white text-gray-700 shadow-sm transition-opacity opacity-0 group-hover:opacity-100"
                            @click="featuredId = item.id">
                        {{ __('Set Featured') }}
                    </button>

                    {{-- Remove Button --}}
                    <button type="button"
                            @click="removeMedia(item.id)"
                            class="absolute top-2 right-2 z-10 bg-white/90 text-red-600 rounded-full p-1.5 shadow-sm hover:bg-white transition-colors border border-gray-200"
                            title="{{ __('Remove') }}">
                        <i class="bi bi-x-lg text-xs"></i>
                    </button>

                    {{-- Alt Text Input --}}
                    <div class="p-2 border-t border-gray-100 bg-white">
                        <input type="text"
                               class="w-full text-xs border-gray-300 rounded px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                               :value="altMap[item.id] ?? ''"
                               @input="altMap[item.id] = $event.target.value"
                               placeholder="{{ __('Alt text') }}"
                               title="{{ __('Alt text') }}"
                               required />
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
                 <p class="text-sm text-gray-600 mb-2 truncate" x-text="previews?.[0]?.name || ''"></p>
             </div>
        </div>

        {{-- Trigger Button --}}
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
