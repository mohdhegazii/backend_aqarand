@props(['inputName', 'label' => 'Choose Media', 'allowedType' => 'image', 'value' => null])

{{-- Button to trigger the modal --}}
<div x-data="{
    openMediaModal() {
        // Dispatch event to open the modal
        window.dispatchEvent(new CustomEvent('open-media-manager', {
            detail: {
                inputName: '{{ $inputName }}',
                allowedType: '{{ $allowedType }}'
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

                // Library State
                mediaItems: [],
                isLoading: false,
                searchQuery: '',
                currentPage: 1,
                lastPage: 1,
                selectedItem: null,

                // Upload State
                uploadFile: null,
                uploadAlt: '',
                isUploading: false,
                uploadProgress: 0,
                uploadError: null,

                init() {
                    window.addEventListener('open-media-manager', (e) => {
                        this.targetInputName = e.detail.inputName;
                        this.allowedType = e.detail.allowedType || 'image';
                        this.isOpen = true;
                        this.fetchMedia();
                    });
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetUpload();
                    this.selectedItem = null;
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
                        // Optionally show error toast
                    } finally {
                        this.isLoading = false;
                    }
                },

                selectItem(item) {
                    this.selectedItem = item;
                },

                confirmSelection() {
                    if (!this.selectedItem || !this.targetInputName) return;

                    // Update hidden input
                    const input = document.querySelector(`input[name="${this.targetInputName}"]`);
                    if (input) {
                        input.value = this.selectedItem.id;
                        // Dispatch change event manually
                        input.dispatchEvent(new Event('change', { bubbles: true }));

                        // Also dispatch a custom event for other listeners (like preview components)
                        window.dispatchEvent(new CustomEvent('media-selected', {
                            detail: {
                                inputName: this.targetInputName,
                                media: this.selectedItem
                            }
                        }));
                    }

                    this.closeModal();
                },

                // --- Upload Methods ---

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.uploadFile = file;
                        // Auto-fill alt text with filename if empty
                        if (!this.uploadAlt) {
                            const name = file.name.split('.').slice(0, -1).join('.');
                            this.uploadAlt = name.replace(/[-_]/g, ' ');
                        }
                    }
                },

                resetUpload() {
                    this.uploadFile = null;
                    this.uploadAlt = '';
                    this.isUploading = false;
                    this.uploadError = null;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                async uploadMedia() {
                    if (!this.uploadFile) return;
                    if (this.allowedType === 'image' && !this.uploadAlt) {
                        this.uploadError = 'Alt text is required for images.';
                        return;
                    }

                    this.isUploading = true;
                    this.uploadError = null;

                    const formData = new FormData();
                    formData.append('file', this.uploadFile);
                    if (this.uploadAlt) {
                        formData.append('alt_text', this.uploadAlt);
                    }

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
                        this.resetUpload();
                        this.activeTab = 'library';
                        this.fetchMedia(1); // Refresh library

                        // Optional: Auto-select uploaded item (if result contains full object or we fetch it)
                        // result usually contains media_id
                        // We might want to just let user see it in list

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
                    // Try to find a thumb variant
                    if (item.variants && item.variants.thumb) {
                        // variants is usually a path or url
                        // Check if variants is object or array. API says 'variants' => $item->variants (JSON)
                        // Need to verify API response structure for variants.
                        // Assuming variants object with keys like 'thumb', 'medium'
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
         style="display: none;"
         class="fixed inset-0 z-[60] overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background backdrop --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="closeModal"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                {{-- Modal Header --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start sm:justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ __('Media Manager') }}
                        </h3>
                        <button @click="closeModal" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    {{-- Tabs --}}
                    <div class="mt-4 border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="setTab('library')"
                                    :class="activeTab === 'library' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('Media Library') }}
                            </button>
                            <button @click="setTab('upload')"
                                    :class="activeTab === 'upload' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('Upload New') }}
                            </button>
                        </nav>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="bg-gray-50 px-4 py-5 sm:p-6 min-h-[400px]">

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
                            <div class="spinner-border text-indigo-600" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Loading media...</p>
                        </div>

                        <div x-show="!isLoading && mediaItems.length === 0" class="text-center py-10">
                            <i class="bi bi-images text-4xl text-gray-300"></i>
                            <p class="mt-2 text-sm text-gray-500">No media found.</p>
                        </div>

                        <div x-show="!isLoading && mediaItems.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <template x-for="item in mediaItems" :key="item.id">
                                <div @click="selectItem(item)"
                                     :class="selectedItem && selectedItem.id === item.id ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 hover:border-gray-300'"
                                     class="relative group cursor-pointer bg-white border rounded-lg overflow-hidden aspect-square flex items-center justify-center">

                                    {{-- Image --}}
                                    <template x-if="item.type === 'image'">
                                        <img :src="getThumbnail(item) || item.url" :alt="item.alt_text" class="w-full h-full object-cover">
                                    </template>

                                    {{-- PDF/Other --}}
                                    <template x-if="item.type !== 'image'">
                                        <div class="text-center p-2">
                                            <i class="bi bi-file-earmark-pdf text-red-500 text-4xl" x-show="item.type === 'pdf'"></i>
                                            <i class="bi bi-file-earmark text-gray-500 text-4xl" x-show="item.type !== 'pdf'"></i>
                                            <p class="mt-1 text-xs text-gray-600 truncate w-full" x-text="item.original_name"></p>
                                        </div>
                                    </template>

                                    {{-- Selected Overlay --}}
                                    <div x-show="selectedItem && selectedItem.id === item.id" class="absolute inset-0 bg-indigo-500 bg-opacity-10 flex items-center justify-center">
                                        <div class="bg-indigo-500 text-white rounded-full p-1">
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                    </div>

                                    {{-- Hover info --}}
                                    <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white text-[10px] p-1 truncate opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span x-text="item.original_name"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4 flex items-center justify-between" x-show="!isLoading && mediaItems.length > 0">
                            <button @click="fetchMedia(currentPage - 1)" :disabled="currentPage <= 1" class="text-sm text-gray-600 hover:text-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                &laquo; {{ __('Previous') }}
                            </button>
                            <span class="text-sm text-gray-500">
                                {{ __('Page') }} <span x-text="currentPage"></span> / <span x-text="lastPage"></span>
                            </span>
                            <button @click="fetchMedia(currentPage + 1)" :disabled="currentPage >= lastPage" class="text-sm text-gray-600 hover:text-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ __('Next') }} &raquo;
                            </button>
                        </div>
                    </div>

                    {{-- Upload Tab --}}
                    <div x-show="activeTab === 'upload'" class="max-w-lg mx-auto">
                        <div class="space-y-4">

                            {{-- Drop Zone / File Input --}}
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors bg-white">
                                <input type="file" x-ref="fileInput" @change="handleFileSelect" class="hidden" :accept="allowedType === 'image' ? 'image/*' : (allowedType === 'pdf' ? 'application/pdf' : '*/*')">
                                <div class="cursor-pointer" @click="$refs.fileInput.click()">
                                    <i class="bi bi-cloud-upload text-4xl text-gray-400"></i>
                                    <p class="mt-2 text-sm text-gray-600" x-show="!uploadFile">{{ __('Click to select a file') }}</p>
                                    <p class="mt-2 text-sm text-indigo-600 font-semibold" x-show="uploadFile" x-text="uploadFile.name"></p>
                                </div>
                            </div>

                            {{-- Alt Text --}}
                            <div x-show="uploadFile">
                                <label class="block text-sm font-medium text-gray-700">{{ __('Alt Text / Description') }} <span class="text-red-500">*</span></label>
                                <input type="text" x-model="uploadAlt" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ __('Required for SEO and accessibility.') }}</p>
                            </div>

                            {{-- Error Message --}}
                            <div x-show="uploadError" class="text-sm text-red-600">
                                <i class="bi bi-exclamation-circle me-1"></i> <span x-text="uploadError"></span>
                            </div>

                            {{-- Upload Button --}}
                            <button @click="uploadMedia"
                                    :disabled="!uploadFile || isUploading"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                <span x-show="isUploading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                <span x-text="isUploading ? '{{ __('Uploading...') }}' : '{{ __('Upload') }}'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100" x-show="activeTab === 'library'">
                    <button type="button"
                            @click="confirmSelection"
                            :disabled="!selectedItem"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('Use Selected') }}
                    </button>
                    <button type="button"
                            @click="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endonce
