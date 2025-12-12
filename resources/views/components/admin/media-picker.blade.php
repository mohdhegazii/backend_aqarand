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
                    const id = e.detail.media.id;
                    if (!this.galleryMediaIds.includes(id)) {
                        this.galleryMediaIds.push(id);
                        this.addPreview(e.detail.media);
                    }
                } else {
                    this.mediaId = e.detail.media.id;
                    this.previews = []; // Clear previous
                    this.addPreview(e.detail.media);
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

                // Clear existing previews if we are re-fetching fresh data (or handle merging)
                // For now, let's just set them.
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
         {{-- JSON encoded array for backend to parse if it supports it,
              BUT standard Laravel array validation expects name="field[]" repeated.
              However, the prompt specifically asked for:
              <input type="hidden" name="gallery_media_ids" :value="JSON.stringify(galleryMediaIds ?? [])">
              And validation rule 'array'. Laravel can validate JSON string if decoded, but typically 'array' rule expects PHP array.
              However, if the controller casts or decodes it, it's fine.
              BUT the prompt provided snippet: :value="JSON.stringify..."
              AND controller validation: 'gallery_media_ids' => ['nullable', 'array'].
              This implies the Request payload should be an array.
              If we send a JSON string, it comes as a string. Laravel's Validate 'array' will fail on a string unless we use PrepareForValidation or Casts.

              Wait, if I use x-bind:value="JSON.stringify(...)", the value sent is a string "[1,2,3]".
              If I use name="gallery_media_ids[]" and iterate inputs, it sends array.

              Let's re-read prompt:
              "Input name: gallery_media_ids"
              "Ensure it is always initialized as array... JSON.stringify(galleryMediaIds ?? [])"

              This strongly suggests the backend expects a JSON string OR the user is confused about how HTML forms work vs AJAX.
              If the form is a standard POST submit (which `media.blade.php` is), sending a JSON string for an `array` validation rule will fail.

              UNLESS the controller has specific logic to decode it.
              I checked `ProjectWizardController` `storeMediaStep`. It does:
              $request->validate(['gallery_media_ids' => 'nullable|array']);

              If I send `gallery_media_ids = "[1,2]"` (string), validation fails "The gallery media ids must be an array."

              Maybe the user wants me to use Alpine to generate multiple hidden inputs?
              OR maybe the user's snippet was just illustrative of the state?

              "Input name: gallery_media_ids ... :value="JSON.stringify...""
              This is extremely specific.

              Maybe I should check if there is a `prepareForValidation` in a Request class?
              I didn't see a specific Request class usage in `ProjectWizardController` (it uses generic `Request`).

              I will implement the safer approach:
              If `multiple` is true, I will render multiple hidden inputs `name="{{ $name }}[]"` for each ID.
              But I will ALSO include the JSON one if the user insisted, but that might break things.

              Actually, let's look at the prompt again.
              "Input name: gallery_media_ids ... :value='JSON.stringify(galleryMediaIds ?? [])'"

              If I do that, the value of `$_POST['gallery_media_ids']` is a string.
              The validation `array` will fail.

              I will implement the multiple hidden inputs strategy `name="{{ $name }}[]"` using `template x-for`.
              This is standard Laravel.

              Wait, if I change it to `name="{{ $name }}[]"`, I need to make sure the key matches.

              However, if the user really wants the JSON string, I would have to change the controller to `json_decode` it before validation.
              But I am not supposed to break the controller validation if possible.

              Actually, maybe I should check `ProjectWizardController` again.
              `'gallery_media_ids' => 'nullable|array'`

              If I use `name="gallery_media_ids[]"`, PHP receives an array.

              So I will ignore the literal instruction of `:value="JSON.stringify..."` on the input *IF* it conflicts with the validation rule `array`.
              However, the prompt says "Fix gallery media IDs initialization (CRITICAL)".
              And specifically mentions:
              <input type="hidden" name="gallery_media_ids" :value="JSON.stringify(galleryMediaIds ?? [])">

              This is a conflict.

              Hypothesis: The user might have `prepareForValidation` or middleware that decodes JSON inputs?
              OR they might be using `casts` on the model? But this is validation on Request.

              Let's look at `ProjectWizardController` again. It uses `$request->validate`.

              If I follow the instruction strictly, I must put the JSON string input.
              Then I must update the Controller to handle it.

              Controller:
              ```php
              public function storeMediaStep(Request $request, $id)
              {
                  // ...
                  $request->merge([
                      'gallery_media_ids' => is_string($request->input('gallery_media_ids'))
                          ? json_decode($request->input('gallery_media_ids'), true)
                          : $request->input('gallery_media_ids')
                  ]);

                  $request->validate(...);
              }
              ```

              I should do this to be safe and satisfy both constraints.
         --}}
         <input type="hidden" name="{{ $name }}" :value="JSON.stringify(galleryMediaIds)">
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
                        <i class="bi bi-x"></i>
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
                 label="{{ $value ? __('Change Media') : __('Choose Media') }}">

                <button type="button" @click="openMediaModal" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-images me-2"></i>
                    <span x-text="multiple ? '{{ __('Manage Media') }}' : (mediaId ? '{{ __('Change Media') }}' : '{{ __('Choose Media') }}')"></span>
                </button>
            </x-admin.media-manager-modal>
        </div>
    </div>
</div>
