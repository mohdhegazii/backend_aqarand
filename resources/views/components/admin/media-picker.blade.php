@props(['name', 'label' => 'Media', 'value' => null])

<div x-data="{
    mediaId: '{{ $value }}',
    previewUrl: null,
    previewName: null,

    init() {
        if (this.mediaId) {
            this.fetchMediaPreview(this.mediaId);
        }

        // Listen for selection from the modal
        window.addEventListener('media-selected', (e) => {
            if (e.detail.inputName === '{{ $name }}') {
                this.mediaId = e.detail.media.id;
                this.updatePreview(e.detail.media);
            }
        });
    },

    async fetchMediaPreview(id) {
        if (!id) return;
        try {
            const response = await fetch(`{{ url('/admin/media') }}/${id}`);
            if (response.ok) {
                const media = await response.json();
                this.updatePreview(media);
            }
        } catch (e) {
            console.error('Error fetching media preview', e);
        }
    },

    updatePreview(media) {
        this.previewUrl = media.variants?.thumb?.url || media.variants?.thumb || media.url;
        this.previewName = media.original_name;
    },

    removeMedia() {
        this.mediaId = '';
        this.previewUrl = null;
        this.previewName = null;
    }
}" class="mb-4">

    <label class="block font-medium text-sm text-gray-700 mb-2">{{ $label }}</label>

    {{-- Hidden Input --}}
    <input type="hidden" name="{{ $name }}" x-model="mediaId">

    {{-- Preview / Selection Area --}}
    <div class="flex items-center space-x-4">

        {{-- Preview Box --}}
        <div class="relative w-24 h-24 border border-gray-300 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden group">
            <template x-if="previewUrl">
                <img :src="previewUrl" class="w-full h-full object-cover">
            </template>
            <template x-if="!previewUrl">
                <i class="bi bi-image text-gray-300 text-3xl"></i>
            </template>

            {{-- Remove Button Overlay --}}
            <button type="button"
                    x-show="previewUrl"
                    @click="removeMedia"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                    title="{{ __('Remove') }}">
                <i class="bi bi-x"></i>
            </button>
        </div>

        {{-- Info & Button --}}
        <div class="flex-1">
            <template x-if="previewName">
                <p class="text-sm text-gray-600 mb-2 truncate" x-text="previewName"></p>
            </template>

            <x-admin.media-manager-modal inputName="{{ $name }}" label="{{ $value ? __('Change Image') : __('Choose Image') }}">
                <button type="button" @click="openMediaModal" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-images me-2"></i> <span x-text="mediaId ? '{{ __('Change Image') }}' : '{{ __('Choose Image') }}'"></span>
                </button>
            </x-admin.media-manager-modal>
        </div>
    </div>
</div>
