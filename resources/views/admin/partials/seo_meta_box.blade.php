<div class="bg-white p-4 rounded shadow border border-gray-200 mt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">@lang('admin.seo_optimization')</h3>

    <div class="grid grid-cols-1 gap-6">
        <!-- Focus Keyphrase -->
        <div>
            <label for="focus_keyphrase" class="block text-sm font-medium text-gray-700">@lang('admin.focus_keyphrase')</label>
            <input type="text" name="seo_meta[focus_keyphrase]" id="focus_keyphrase"
                   value="{{ old('seo_meta.focus_keyphrase', $model->seoMeta->focus_keyphrase ?? '') }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            <p class="mt-1 text-xs text-gray-500">@lang('admin.focus_keyphrase_help')</p>
        </div>

        <!-- Meta Title -->
        <div>
            <label for="meta_title" class="block text-sm font-medium text-gray-700">@lang('admin.seo_title')</label>
            <input type="text" name="seo_meta[meta_title]" id="meta_title"
                   value="{{ old('seo_meta.meta_title', $model->seoMeta->meta_title ?? '') }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                   oninput="updateSnippet()">
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>@lang('admin.seo_title_help')</span>
                <span id="title-count">0 / 60</span>
            </div>
            <div id="title-bar" class="h-1 bg-gray-200 mt-1 rounded">
                <div id="title-progress" class="h-1 bg-green-500 rounded" style="width: 0%"></div>
            </div>
        </div>

        <!-- Meta Description -->
        <div>
            <label for="meta_description" class="block text-sm font-medium text-gray-700">@lang('admin.meta_description')</label>
            <textarea name="seo_meta[meta_description]" id="meta_description" rows="3"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      oninput="updateSnippet()">{{ old('seo_meta.meta_description', $model->seoMeta->meta_description ?? '') }}</textarea>
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>@lang('admin.meta_description_help')</span>
                <span id="desc-count">0 / 160</span>
            </div>
             <div id="desc-bar" class="h-1 bg-gray-200 mt-1 rounded">
                <div id="desc-progress" class="h-1 bg-green-500 rounded" style="width: 0%"></div>
            </div>
        </div>

        <!-- Snippet Preview -->
        <div class="mt-4 p-4 bg-gray-50 border rounded">
            <h4 class="text-sm font-bold text-gray-500 uppercase mb-2">@lang('admin.snippet_preview')</h4>
            <div class="font-sans">
                <div class="text-sm text-gray-600 mb-1">example.com › ...</div>
                <div class="text-xl text-blue-800 font-medium hover:underline cursor-pointer" id="snippet-title">
                    {{ $model->seoMeta->meta_title ?? 'SEO Title' }}
                </div>
                <div class="text-sm text-gray-600" id="snippet-desc">
                    {{ $model->seoMeta->meta_description ?? 'Meta description...' }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateSnippet() {
        const titleInput = document.getElementById('meta_title');
        const descInput = document.getElementById('meta_description');
        const snippetTitle = document.getElementById('snippet-title');
        const snippetDesc = document.getElementById('snippet-desc');
        const titleCount = document.getElementById('title-count');
        const descCount = document.getElementById('desc-count');
        const titleProgress = document.getElementById('title-progress');
        const descProgress = document.getElementById('desc-progress');

        const titleVal = titleInput.value;
        const descVal = descInput.value;

        snippetTitle.innerText = titleVal || 'SEO Title';
        snippetDesc.innerText = descVal || 'Meta description...';

        titleCount.innerText = titleVal.length + ' / 60';
        descCount.innerText = descVal.length + ' / 160';

        // Simple progress bars
        let tWidth = Math.min((titleVal.length / 60) * 100, 100);
        titleProgress.style.width = tWidth + '%';
        if (titleVal.length > 60) titleProgress.classList.replace('bg-green-500', 'bg-red-500');
        else titleProgress.classList.replace('bg-red-500', 'bg-green-500');

        let dWidth = Math.min((descVal.length / 160) * 100, 100);
        descProgress.style.width = dWidth + '%';
        if (descVal.length > 160) descProgress.classList.replace('bg-green-500', 'bg-red-500');
        else descProgress.classList.replace('bg-red-500', 'bg-green-500');
    }

    document.addEventListener('DOMContentLoaded', updateSnippet);
</script>
