@extends('admin.layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Media Manager (New)') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="mb-4 text-gray-600 text-sm">
                        This is the new centralized Media Manager. The legacy file manager is still available at
                        <a href="{{ localized_route('admin.file_manager') }}" class="text-blue-500 hover:underline">/admin/file-manager</a>.
                    </p>

                    <div id="media-manager-container" x-data="mediaManagerPage()" x-init="init()">

                        {{-- Toolbar --}}
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <input type="text"
                                       x-model.debounce.500ms="searchQuery"
                                       @input="fetchMedia(1)"
                                       placeholder="{{ __('Search by name, alt text...') }}"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full md:w-64 sm:text-sm border-gray-300 rounded-md">

                                <select x-model="filterType" @change="fetchMedia(1)" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full md:w-40 sm:text-sm border-gray-300 rounded-md">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="image">{{ __('Images') }}</option>
                                    <option value="pdf">{{ __('PDFs') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                            </div>

                            <button @click="showUploadModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="bi bi-cloud-upload me-2"></i> {{ __('Upload New') }}
                            </button>
                        </div>

                        {{-- Grid --}}
                        <div x-show="isLoading" class="text-center py-20">
                            <div class="spinner-border text-indigo-600" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-4 text-gray-500">{{ __('Loading library...') }}</p>
                        </div>

                        <div x-show="!isLoading && mediaItems.length === 0" class="text-center py-20 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                            <i class="bi bi-images text-5xl text-gray-300 mb-4 block"></i>
                            <p class="text-gray-500 font-medium">{{ __('No media items found.') }}</p>
                            <p class="text-sm text-gray-400 mt-1">{{ __('Try adjusting your search or upload a new file.') }}</p>
                        </div>

                        <div x-show="!isLoading && mediaItems.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                            <template x-for="item in mediaItems" :key="item.id">
                                <div class="relative group bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    {{-- Image Preview --}}
                                    <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                                        <template x-if="item.type === 'image'">
                                            <img :src="getThumbnail(item) || item.url" :alt="item.alt_text" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="item.type !== 'image'">
                                            <div class="text-center p-4">
                                                <i class="bi bi-file-earmark-pdf text-red-500 text-5xl" x-show="item.type === 'pdf'"></i>
                                                <i class="bi bi-file-earmark text-gray-400 text-5xl" x-show="item.type !== 'pdf'"></i>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Info --}}
                                    <div class="p-3">
                                        <p class="text-xs font-semibold text-gray-700 truncate" :title="item.original_name" x-text="item.original_name"></p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-[10px] text-gray-500 uppercase tracking-wider" x-text="item.type"></span>

                                            {{-- Actions --}}
                                            <div class="flex space-x-2">
                                                <button @click="copyUrl(item.url)" class="text-gray-400 hover:text-indigo-600 transition-colors" title="{{ __('Copy URL') }}">
                                                    <i class="bi bi-link-45deg text-lg"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Delete') }}">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-8 flex items-center justify-between" x-show="!isLoading && mediaItems.length > 0">
                            <button @click="fetchMedia(currentPage - 1)" :disabled="currentPage <= 1" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                {{ __('Previous') }}
                            </button>
                            <span class="text-sm text-gray-700">
                                {{ __('Page') }} <span class="font-medium" x-text="currentPage"></span> {{ __('of') }} <span class="font-medium" x-text="lastPage"></span>
                            </span>
                            <button @click="fetchMedia(currentPage + 1)" :disabled="currentPage >= lastPage" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                {{ __('Next') }}
                            </button>
                        </div>

                        {{-- Upload Modal (Inline reuse logic) --}}
                        <div x-show="showUploadModal" style="display: none;" class="fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUploadModal = false"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <i class="bi bi-cloud-upload text-indigo-600 text-lg"></i>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ltr:ml-4 sm:rtl:mr-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Upload Media') }}</h3>
                                            <div class="mt-4 space-y-4">
                                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors bg-gray-50 cursor-pointer" @click="$refs.pageFileInput.click()">
                                                    <input type="file" x-ref="pageFileInput" @change="handleFileSelect" class="hidden">
                                                    <i class="bi bi-cloud-arrow-up text-3xl text-gray-400"></i>
                                                    <p class="mt-2 text-sm text-gray-600" x-show="!uploadFile">{{ __('Click to select file') }}</p>
                                                    <p class="mt-2 text-sm text-indigo-600 font-semibold" x-show="uploadFile" x-text="uploadFile.name"></p>
                                                </div>
                                                <div x-show="uploadFile">
                                                    <label class="block text-sm font-medium text-gray-700 text-left">{{ __('Alt Text') }} <span class="text-red-500">*</span></label>
                                                    <input type="text" x-model="uploadAlt" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                </div>
                                                <div x-show="uploadError" class="text-sm text-red-600 text-left">
                                                    <i class="bi bi-exclamation-circle me-1"></i> <span x-text="uploadError"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="button" @click="uploadMedia" :disabled="!uploadFile || isUploading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                            <span x-show="isUploading" class="spinner-border spinner-border-sm me-2"></span>
                                            {{ __('Upload') }}
                                        </button>
                                        <button type="button" @click="showUploadModal = false; resetUpload()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function mediaManagerPage() {
            return {
                mediaItems: [],
                isLoading: false,
                searchQuery: '',
                filterType: '',
                currentPage: 1,
                lastPage: 1,

                // Upload Modal State
                showUploadModal: false,
                uploadFile: null,
                uploadAlt: '',
                isUploading: false,
                uploadError: null,

                init() {
                    this.fetchMedia();
                },

                async fetchMedia(page = 1) {
                    this.isLoading = true;
                    this.currentPage = page;

                    let url = `{{ localized_route('admin.media.index') }}?page=${page}`;
                    if (this.searchQuery) url += `&search=${encodeURIComponent(this.searchQuery)}`;
                    if (this.filterType) url += `&type=${this.filterType}`;

                    try {
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const result = await response.json();
                        this.mediaItems = result.data;
                        this.lastPage = result.meta.last_page;
                    } catch (error) {
                        console.error('Fetch error:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.uploadFile = file;
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
                    if (this.$refs.pageFileInput) this.$refs.pageFileInput.value = '';
                },

                async uploadMedia() {
                    if (!this.uploadFile) return;
                    if (!this.uploadAlt) {
                        this.uploadError = 'Alt text is required.';
                        return;
                    }

                    this.isUploading = true;
                    this.uploadError = null;
                    const formData = new FormData();
                    formData.append('file', this.uploadFile);
                    formData.append('alt_text', this.uploadAlt);

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        const response = await fetch('{{ localized_route('admin.media.upload') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: formData
                        });
                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message || 'Upload failed');

                        this.showUploadModal = false;
                        this.resetUpload();
                        this.fetchMedia(1);
                    } catch (error) {
                        this.uploadError = error.message;
                    } finally {
                        this.isUploading = false;
                    }
                },

                async confirmDelete(item) {
                    if (!confirm(`Are you sure you want to delete "${item.original_name}"?`)) return;

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        // We use a placeholder '0' and replace it with the item ID
                        const url = '{{ localized_route('admin.media.destroy', '0') }}'.replace('/0', '/' + item.id);

                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                        });
                        if (response.ok) {
                            this.fetchMedia(this.currentPage);
                        } else {
                            alert('Failed to delete media.');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                    }
                },

                copyUrl(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('URL copied to clipboard!');
                    });
                },

                getThumbnail(item) {
                     if (item.variants && item.variants.thumb) {
                        return item.variants.thumb.url || item.variants.thumb;
                    }
                    return null;
                }
            }
        }
    </script>
    @endpush
@endsection
