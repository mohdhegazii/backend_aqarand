@props(['name', 'url', 'placeholder' => '', 'selectedId' => null, 'selectedText' => ''])

<div x-data='{
        open: false,
        loading: false,
        search: @json($selectedText ?? ""),
        selectedId: @json($selectedId),
        options: [],
        placeholder: @json($placeholder ?? __("admin.select_option")),
        url: @json($url),
        // Allows clearing search buffer if needed
        minChars: 0,

        init() {
            // Pre-populate text if we have a selected ID but no text?
            // Usually backend should provide both.
            // If we have selectedId but no text, we might want to fetch it, but lets rely on passed props for now.

            this.$watch("open", value => {
                // Fetch default options on open if empty and no specific search
                if (value && this.options.length === 0 && !this.selectedId) {
                    this.fetchOptions();
                }
            });
        },

        fetchOptions() {
            this.loading = true;
            const params = new URLSearchParams({ q: this.search });

            // Handle existing query params in URL
            const separator = this.url.includes("?") ? "&" : "?";
            const fullUrl = `${this.url}${separator}${params.toString()}`;

            fetch(fullUrl)
                .then(response => {
                    if (!response.ok) throw new Error("Network response was not ok");
                    return response.json();
                })
                .then(data => {
                    this.options = data;
                    this.loading = false;
                })
                .catch(() => {
                    this.options = [];
                    this.loading = false;
                });
        },

        selectOption(option) {
            this.selectedId = option.id;
            this.search = option.text || option.name;
            this.open = false;
            this.options = [];
            // Dispatch event for dependencies
            this.$dispatch("change", { value: this.selectedId });
        },

        clearSelection() {
            this.selectedId = null;
            this.search = "";
            this.options = [];
            this.open = true; // Re-open to show default options
            this.fetchOptions();
            this.$dispatch("change", { value: null });
        },

        onInput() {
            // When user types, clear the ID until they select something
            // unless we want to allow free text (unlikely for foreign keys)
            if (this.selectedId) {
                this.selectedId = null;
            }
            this.fetchOptions();
        }
    }'
    class="relative"
    {{ $attributes }}
>
    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $name }}" :value="selectedId" value="{{ $selectedId }}">

    <!-- Display Input (Search) -->
    <div class="relative">
        <input
            type="text"
            x-model="search"
            @input.debounce.300ms="onInput()"
            @focus="open = true"
            @click.away="open = false"
            @keydown.escape="open = false"
            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm pl-3 pr-10 {{ $errors->has($name) ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }}"
            :placeholder="placeholder"
            autocomplete="off"
        >

        <!-- Clear Button -->
        <button type="button" x-show="selectedId" @click="clearSelection()" class="absolute inset-y-0 right-8 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
             <i class="bi bi-x-circle-fill"></i>
        </button>

        <!-- Loading Spinner -->
        <div x-show="loading" class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
             <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Dropdown Results -->
    <div x-show="open && (options.length > 0 || (search.length > 0 && !selectedId))"
         class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
         style="display: none;"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
    >
        <template x-if="options.length === 0 && !loading && search.length > 0">
            <div class="relative cursor-default select-none py-2 px-4 text-gray-700">
                {{ __('admin.no_results_found') }}
            </div>
        </template>

        <template x-for="option in options" :key="option.id">
            <div
                @click="selectOption(option)"
                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
            >
                <div class="flex items-center">
                    <span x-text="option.text || option.name" class="font-normal block truncate"></span>
                </div>
            </div>
        </template>
    </div>
</div>
