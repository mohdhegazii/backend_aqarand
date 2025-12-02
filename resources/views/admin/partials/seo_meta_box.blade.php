@php
    $locale = $locale ?? app()->getLocale();
    // Determine field names based on locale
    $titleField = 'meta_title_' . $locale;
    $descField = 'meta_description_' . $locale;
    $keywordField = 'focus_keyphrase_' . $locale;

    // Fallback for non-bilingual seoMeta (if any)
    if (!isset($model->seoMeta->$titleField) && isset($model->seoMeta->meta_title)) {
        // This case handles if we haven't migrated data but want to show something,
        // but for this task we assume migration runs.
    }

    $titleValue = old("seo_meta.{$titleField}", $model->seoMeta->$titleField ?? '');
    $descValue = old("seo_meta.{$descField}", $model->seoMeta->$descField ?? '');
    $keywordValue = old("seo_meta.{$keywordField}", $model->seoMeta->$keywordField ?? '');

    $prefix = "seo_box_{$locale}";
@endphp

<div class="bg-white p-4 rounded shadow border border-gray-200 mt-6" id="{{ $prefix }}">
    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">
        @lang('admin.seo_optimization') ({{ strtoupper($locale) }})
    </h3>

    <div class="grid grid-cols-1 gap-6">
        <!-- Focus Keyphrase -->
        <div>
            <label for="{{ $prefix }}_focus_keyphrase" class="block text-sm font-medium text-gray-700">@lang('admin.focus_keyphrase')</label>
            <input type="text" name="seo_meta[{{ $keywordField }}]" id="{{ $prefix }}_focus_keyphrase"
                   value="{{ $keywordValue }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md seo-input">
            <p class="mt-1 text-xs text-gray-500">@lang('admin.focus_keyphrase_help')</p>
        </div>

        <!-- Meta Title -->
        <div>
            <label for="{{ $prefix }}_meta_title" class="block text-sm font-medium text-gray-700">@lang('admin.seo_title')</label>
            <input type="text" name="seo_meta[{{ $titleField }}]" id="{{ $prefix }}_meta_title"
                   value="{{ $titleValue }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md seo-input">
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>@lang('admin.seo_title_help')</span>
                <span class="char-count">0 / 60</span>
            </div>
            <!-- Traffic Lights for Title -->
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="seo-light title-length-light w-3 h-3 rounded-full bg-gray-300"></span>
                <span class="text-gray-600 title-length-msg">Length check</span>

                <span class="seo-light title-keyword-light w-3 h-3 rounded-full bg-gray-300 ml-4"></span>
                <span class="text-gray-600 title-keyword-msg">Keyphrase check</span>
            </div>
        </div>

        <!-- Meta Description -->
        <div>
            <label for="{{ $prefix }}_meta_description" class="block text-sm font-medium text-gray-700">@lang('admin.meta_description')</label>
            <textarea name="seo_meta[{{ $descField }}]" id="{{ $prefix }}_meta_description" rows="3"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md seo-input">{{ $descValue }}</textarea>
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>@lang('admin.meta_description_help')</span>
                <span class="char-count">0 / 160</span>
            </div>
             <!-- Traffic Lights for Description -->
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="seo-light desc-length-light w-3 h-3 rounded-full bg-gray-300"></span>
                <span class="text-gray-600 desc-length-msg">Length check</span>

                <span class="seo-light desc-keyword-light w-3 h-3 rounded-full bg-gray-300 ml-4"></span>
                <span class="text-gray-600 desc-keyword-msg">Keyphrase check</span>
            </div>
        </div>

        <!-- Snippet Preview -->
        <div class="mt-4 p-4 bg-gray-50 border rounded">
            <h4 class="text-sm font-bold text-gray-500 uppercase mb-2">@lang('admin.snippet_preview')</h4>
            <div class="font-sans">
                <div class="text-sm text-gray-600 mb-1">example.com › ...</div>
                <div class="text-xl text-blue-800 font-medium hover:underline cursor-pointer snippet-title">
                    {{ $titleValue ?: 'SEO Title' }}
                </div>
                <div class="text-sm text-gray-600 snippet-desc">
                    {{ $descValue ?: 'Meta description...' }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .seo-good { background-color: #22c55e; } /* Green */
    .seo-medium { background-color: #f97316; } /* Orange */
    .seo-bad { background-color: #ef4444; } /* Red */
    .seo-gray { background-color: #d1d5db; }
</style>

<script>
    (function() {
        const container = document.getElementById('{{ $prefix }}');
        const titleInput = container.querySelector('#{{ $prefix }}_meta_title');
        const descInput = container.querySelector('#{{ $prefix }}_meta_description');
        const keywordInput = container.querySelector('#{{ $prefix }}_focus_keyphrase');

        const snippetTitle = container.querySelector('.snippet-title');
        const snippetDesc = container.querySelector('.snippet-desc');

        const titleLengthLight = container.querySelector('.title-length-light');
        const titleKeywordLight = container.querySelector('.title-keyword-light');
        const descLengthLight = container.querySelector('.desc-length-light');
        const descKeywordLight = container.querySelector('.desc-keyword-light');

        function updateAnalysis() {
            const title = titleInput.value.trim();
            const desc = descInput.value.trim();
            const keyword = keywordInput.value.trim().toLowerCase();

            // Update Snippet
            snippetTitle.innerText = title || 'SEO Title';
            snippetDesc.innerText = desc || 'Meta description...';

            // Char Counts
            titleInput.nextElementSibling.querySelector('.char-count').innerText = title.length + ' / 60';
            descInput.nextElementSibling.querySelector('.char-count').innerText = desc.length + ' / 160';

            // 1. Title Length
            let tLen = title.length;
            if (tLen === 0) setLight(titleLengthLight, 'gray');
            else if (tLen >= 40 && tLen <= 60) setLight(titleLengthLight, 'good');
            else if (tLen >= 30 && tLen <= 65) setLight(titleLengthLight, 'medium');
            else setLight(titleLengthLight, 'bad');

            // 2. Title Keyword
            if (!keyword) setLight(titleKeywordLight, 'gray');
            else if (title.toLowerCase().includes(keyword)) setLight(titleKeywordLight, 'good');
            else setLight(titleKeywordLight, 'bad');

            // 3. Desc Length
            let dLen = desc.length;
            if (dLen === 0) setLight(descLengthLight, 'gray');
            else if (dLen >= 120 && dLen <= 160) setLight(descLengthLight, 'good');
            else if (dLen >= 80 && dLen <= 180) setLight(descLengthLight, 'medium');
            else setLight(descLengthLight, 'bad');

            // 4. Desc Keyword
            if (!keyword) setLight(descKeywordLight, 'gray');
            else if (desc.toLowerCase().includes(keyword)) setLight(descKeywordLight, 'good');
            else setLight(descKeywordLight, 'bad');
        }

        function setLight(element, status) {
            element.classList.remove('seo-good', 'seo-medium', 'seo-bad', 'seo-gray', 'bg-gray-300');
            element.classList.add('seo-' + status);
        }

        titleInput.addEventListener('input', updateAnalysis);
        descInput.addEventListener('input', updateAnalysis);
        keywordInput.addEventListener('input', updateAnalysis);

        // Init
        updateAnalysis();
    })();
</script>
