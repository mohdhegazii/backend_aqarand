@props(['inputName', 'label' => 'Choose Media', 'allowedType' => 'image', 'value' => null, 'multiple' => false])

{{-- Button to trigger the modal --}}
<div x-data="{
    openMediaModal() {
        // Dispatch event to open the modal
        window.dispatchEvent(new CustomEvent('open-media-manager', {
            detail: {
                inputName: '{{ $inputName }}',
                allowedType: '{{ $allowedType }}',
                multiple: {{ $multiple ? 'true' : 'false' }}
            }
        }));
    }
}">
    {{-- This slot allows custom trigger button or uses default --}}
    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <button type="button" @click="openMediaModal" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="bi bi-images me-2"></i> {{ $label }}
        </button>
    @endif
</div>

{{-- The Modal Component --}}
@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mediaManagerModal', () => ({
                isOpen: false,
                activeTab: 'library', // library, upload
                targetInputName: null,
                allowedType: 'image',
                multiple: false,

                // Library State
                mediaItems: [],
                isLoading: false,
                searchQuery: '',
                currentPage: 1,
                lastPage: 1,
                selectedItem: null, // For single selection
                selectedItems: [], // For multiple selection (array of objects)

                // Upload State
                uploadFiles: [],
                fileMeta: [], // Array of { name: string, alt_text: string }
                isUploading: false,
                uploadProgress: 0,
                uploadError: null,

                init() {
                    window.addEventListener('open-media-manager', (e) => {
                        this.targetInputName = e.detail.inputName;
                        this.allowedType = e.detail.allowedType || 'image';
                        this.multiple = e.detail.multiple || false;
                        this.isOpen = true;

                        // Reset selection
                        this.selectedItem = null;
                        this.selectedItems = [];

                        this.fetchMedia();
                    });
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetUpload();
                    this.selectedItem = null;
                    this.selectedItems = [];
                },

                setTab(tab) {
                    this.activeTab = tab;
                },

                // --- Library Methods ---

                async fetchMedia(page = 1) {
                    this.isLoading = true;
                    this.currentPage = page;

                    let url = `{{ localized_route('admin.media.index') }}?page=${page}`;
                    if (this.searchQuery) {
                        url += `&search=${encodeURIComponent(this.searchQuery)}`;
                    }
                    if (this.allowedType && this.allowedType !== 'all') {
                        url += `&type=${this.allowedType}`;
                    }

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();

                        this.mediaItems = result.data;
                        this.lastPage = result.meta.last_page;
                    } catch (error) {
                        console.error('Error fetching media:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                selectItem(item) {
                    if (this.multiple) {
                        // Toggle selection
                        const index = this.selectedItems.findIndex(i => i.id === item.id);
                        if (index > -1) {
                            this.selectedItems.splice(index, 1);
                        } else {
                            this.selectedItems.push(item);
                        }
                    } else {
                        // Single selection
                        this.selectedItem = item;
                    }
                },

                isSelected(item) {
                    if (this.multiple) {
                        return this.selectedItems.some(i => i.id === item.id);
                    }
                    return this.selectedItem && this.selectedItem.id === item.id;
                },

                clearSelection() {
                    this.selectedItem = null;
                    this.selectedItems = [];
                },

                confirmSelection() {
                    if (this.multiple && this.selectedItems.length === 0) return;
                    if (!this.multiple && !this.selectedItem) return;
                    if (!this.targetInputName) return;

                    // Prepare payload
                    const mediaPayload = this.multiple ? this.selectedItems : this.selectedItem;

                    // Dispatch a custom event for other listeners
                    window.dispatchEvent(new CustomEvent('media-selected', {
                        detail: {
                            inputName: this.targetInputName,
                            media: mediaPayload
                        }
                    }));

                    this.closeModal();
                },

                // --- Upload Methods ---

                suggestAltFromFilename(name) {
                    return name
                        .replace(/\.[^/.]+$/, '')         // remove extension
                        .replace(/[-_]+/g, ' ')
                        .trim();
                },

                handleFileSelect(event) {
                    const files = Array.from(event.target.files || []);
                    if (files.length > 0) {
                        this.uploadFiles = files;

                        // Build meta array based on files
                        this.fileMeta = files.map(f => ({
                            name: f.name,
                            alt_text: this.suggestAltFromFilename(f.name),
                        }));
                    }
                },

                resetUpload() {
                    this.uploadFiles = [];
                    this.fileMeta = [];
                    this.isUploading = false;
                    this.uploadError = null;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                async uploadMedia() {
                    if (!this.uploadFiles.length) return;

                    // Client-side validation: check if alt texts are present
                    if (this.allowedType === 'image') {
                        const missing = this.fileMeta.some(x => !x.alt_text || !x.alt_text.trim());
                        if (missing) {
                            this.uploadError = 'Alt Text is required for all files.';
                            return;
                        }
                    }

                    this.isUploading = true;
                    this.uploadError = null;

                    const formData = new FormData();

                    // Append all files as 'files[]'
                    this.uploadFiles.forEach((file, i) => {
                        formData.append('files[]', file);
                        // Append corresponding alt text
                        formData.append(`alts[${i}]`, this.fileMeta[i]?.alt_text ?? '');
                    });

                    // Add CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        const response = await fetch('{{ localized_route('admin.media.upload') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message || 'Upload failed');
                        }

                        // Success
                        // Switch to library
                        this.activeTab = 'library';
                        this.resetUpload();

                        // Refresh library
                        await this.fetchMedia(1);

                        // Handle uploaded items selection
                        if (result.uploaded && Array.isArray(result.uploaded)) {
                            // If multiple mode, select all uploaded
                            // If single mode, select the last one

                            const uploadedIds = result.uploaded.map(u => u.id);

                            // Find them in the newly fetched items
                            const newItems = this.mediaItems.filter(item => uploadedIds.includes(item.id));

                            if (this.multiple) {
                                // Add to selection
                                newItems.forEach(item => {
                                    if (!this.selectedItems.some(si => si.id === item.id)) {
                                        this.selectedItems.push(item);
                                    }
                                });
                            } else {
                                // Select the first one found (usually latest)
                                if (newItems.length > 0) {
                                    this.selectedItem = newItems[0];
                                }
                            }
                        }
                        // Fallback for legacy single response
                        else if (result.media_id) {
                             const uploadedItem = this.mediaItems.find(i => i.id === result.media_id);
                             if (uploadedItem) {
                                 if (this.multiple) {
                                    if (!this.selectedItems.some(si => si.id === uploadedItem.id)) {
                                        this.selectedItems.push(uploadedItem);
                                    }
                                 } else {
                                     this.selectedItem = uploadedItem;
                                 }
                             }
                        }

                    } catch (error) {
                        console.error('Upload error:', error);
                        this.uploadError = error.message;
                    } finally {
                        this.isUploading = false;
                    }
                },

                // Helpers
                getThumbnail(item) {
                    if (item.type !== 'image') {
                         return null; // Return generic icon in template
                    }
                    if (item.variants && item.variants.thumb) {
                        return item.variants.thumb.url || item.variants.thumb;
                    }
                    return item.url;
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString();
                }
            }));
        });
    </script>
    @endpush

    {{-- Modal HTML --}}
    <div x-data="mediaManagerModal"
         x-show="isOpen"
         x-cloak
         class="fixed inset-0 z-[9999]"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/60 transition-opacity"
             @click="closeModal"
             aria-hidden="true"></div>

        {{-- Panel --}}
        <div class="relative mx-auto mt-10 w-[95vw] max-w-6xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b bg-gray-50 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                    {{ __('Media Manager') }}
                </h3>
                <button type="button" class="p-2 rounded hover:bg-gray-200 text-gray-500 hover:text-gray-700" @click="closeModal">
                    <span class="sr-only">Close</span>
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5 overflow-auto flex-1 min-h-[400px]">

                {{-- Tabs --}}
                <div class="mb-5 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button type="button" @click="setTab('library')"
                                :class="activeTab === 'library' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            {{ __('Media Library') }}
                        </button>
                        <button type="button" @click="setTab('upload')"
                                :class="activeTab === 'upload' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            {{ __('Upload New') }}
                        </button>
                    </nav>
                </div>

                {{-- Library Tab --}}
                <div x-show="activeTab === 'library'">
                    <div class="flex flex-col sm:flex-row justify-between mb-4 gap-2">
                        <input type="text"
                               x-model.debounce.500ms="searchQuery"
                               @input="fetchMedia(1)"
                               placeholder="{{ __('Search media...') }}"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:w-1/3 sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div x-show="isLoading" class="text-center py-10">
                        <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Loading media...</p>
                    </div>

                    <div x-show="!isLoading && mediaItems.length === 0" class="text-center py-10">
                        <i class="bi bi-images text-4xl text-gray-300"></i>
                        <p class="mt-2 text-sm text-gray-500">No media found.</p>
                    </div>

                    {{-- Responsive Grid: Up to 8 columns on XL screens --}}
                    <div x-show="!isLoading && mediaItems.length > 0"
                         class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
                        <template x-for="item in mediaItems" :key="item.id">
                            <div @click="selectItem(item)"
                                 :class="isSelected(item) ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 hover:border-gray-300'"
                                 class="relative group cursor-pointer bg-white border rounded-lg overflow-hidden aspect-square flex items-center justify-center">

                                <template x-if="item.type === 'image'">
                                    <img :src="getThumbnail(item) || item.url"
                                         :alt="item.alt_text"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='/admin-assets/placeholders/media-missing.svg';">
                                </template>

                                <template x-if="item.type !== 'image'">
                                    <div class="text-center p-2">
                                        <i class="bi bi-file-earmark-pdf text-red-500 text-4xl" x-show="item.type === 'pdf'"></i>
                                        <i class="bi bi-file-earmark text-gray-500 text-4xl" x-show="item.type !== 'pdf'"></i>
                                        <p class="mt-1 text-xs text-gray-600 truncate w-full" x-text="item.original_name"></p>
                                    </div>
                                </template>

                                <div x-show="isSelected(item)" class="absolute inset-0 bg-indigo-500 bg-opacity-10 flex items-center justify-center">
                                    <div class="bg-indigo-500 text-white rounded-full p-1">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                </div>

                                <template x-if="multiple && isSelected(item)">
                                    <div class="absolute top-1 right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center shadow-sm">
                                        <span x-text="selectedItems.findIndex(i => i.id === item.id) + 1"></span>
                                    </div>
                                </template>

                                <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white text-[10px] p-1 truncate opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span x-text="item.original_name"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex items-center justify-between" x-show="!isLoading && mediaItems.length > 0">
                        <button type="button" @click="fetchMedia(currentPage - 1)" :disabled="currentPage <= 1" class="text-sm text-gray-600 hover:text-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            &laquo; {{ __('Previous') }}
                        </button>
                        <span class="text-sm text-gray-500">
                            {{ __('Page') }} <span x-text="currentPage"></span> / <span x-text="lastPage"></span>
                        </span>
                        <button type="button" @click="fetchMedia(currentPage + 1)" :disabled="currentPage >= lastPage" class="text-sm text-gray-600 hover:text-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('Next') }} &raquo;
                        </button>
                    </div>
                </div>

                {{-- Upload Tab --}}
                <div x-show="activeTab === 'upload'" class="max-w-lg mx-auto">
                    <div class="space-y-4">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors bg-white">
                            <input type="file"
                                   x-ref="fileInput"
                                   @change="handleFileSelect"
                                   class="hidden"
                                   multiple
                                   :accept="allowedType === 'image' ? 'image/*' : (allowedType === 'pdf' ? 'application/pdf' : '*/*')">

                            <div class="cursor-pointer" @click="$refs.fileInput.click()">
                                <i class="bi bi-cloud-upload text-4xl text-gray-400"></i>
                                <template x-if="uploadFiles.length === 0">
                                    <p class="mt-2 text-sm text-gray-600">{{ __('Click to select files') }}</p>
                                </template>
                                <template x-if="uploadFiles.length === 1">
                                    <p class="mt-2 text-sm text-indigo-600 font-semibold" x-text="uploadFiles[0].name"></p>
                                </template>
                                <template x-if="uploadFiles.length > 1">
                                    <p class="mt-2 text-sm text-indigo-600 font-semibold">
                                        <span x-text="uploadFiles.length"></span> {{ __('files selected') }}
                                    </p>
                                </template>
                            </div>
                            <button type="button" @click="$refs.fileInput.click()" class="mt-3 inline-flex items-center px-3 py-1 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Choose Files') }}
                            </button>
                        </div>

                        <div x-show="uploadFiles.length > 0">
                            <template x-if="uploadFiles.length === 0">
                                <p class="text-sm text-gray-500">{{ __('Choose files to see Alt Text fields.') }}</p>
                            </template>
                            <template x-if="uploadFiles.length > 0">
                                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                                    <p class="text-sm text-gray-600" x-text="uploadFiles.length + ' {{ __('files selected') }}'"></p>
                                    <template x-for="(meta, idx) in fileMeta" :key="meta.name + idx">
                                        <div class="border rounded p-3 bg-gray-50">
                                            <div class="text-xs text-gray-600 mb-2 truncate" :title="meta.name">
                                                <span x-text="(idx+1) + ') ' + meta.name"></span>
                                            </div>
                                            <label class="block text-sm font-medium mb-1">
                                                {{ __('Alt Text') }} <span class="text-red-600">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                class="w-full border rounded px-3 py-2 text-sm"
                                                x-model="fileMeta[idx].alt_text"
                                                :placeholder="'Alt text for ' + meta.name"
                                                required
                                            />
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div x-show="uploadError" class="text-sm text-red-600">
                            <i class="bi bi-exclamation-circle me-1"></i> <span x-text="uploadError"></span>
                        </div>

                        <button type="button"
                                @click="uploadMedia"
                                :disabled="uploadFiles.length === 0 || isUploading"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <svg x-show="isUploading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isUploading ? '{{ __('Uploading...') }}' : '{{ __('Upload') }}'"></span>
                        </button>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-5 py-4 border-t bg-gray-50 flex-shrink-0">
                <div class="text-sm text-gray-600">
                    <span x-show="multiple" x-text="selectedItems.length ? `Selected: ${selectedItems.length}` : 'No selection'"></span>
                    <span x-show="!multiple" x-text="selectedItem ? 'Selected: 1' : 'No selection'"></span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="px-4 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" @click="closeModal">{{ __('Cancel') }}</button>
                    <button type="button" class="px-4 py-2 rounded bg-indigo-600 text-white disabled:opacity-50"
                            :disabled="(multiple && selectedItems.length===0) || (!multiple && !selectedItem)"
                            @click="confirmSelection">{{ __('Use Selected') }}</button>
                </div>
            </div>

        </div>
    </div>
@endonce
