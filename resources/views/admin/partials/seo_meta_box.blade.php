<div class="bg-white p-4 rounded shadow border border-gray-200 mt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">
        @if($locale === 'en')
            @lang('admin.seo_optimization') (English)
        @else
            @lang('admin.seo_optimization') (Arabic)
        @endif
    </h3>

    <div class="grid grid-cols-1 gap-6">
        <!-- Focus Keyphrase -->
        <div>
            <label for="focus_keyphrase_{{ $locale }}" class="block text-sm font-medium text-gray-700">@lang('admin.focus_keyphrase')</label>
            <input type="text" name="seo_meta[focus_keyphrase_{{ $locale }}]" id="focus_keyphrase_{{ $locale }}"
                   value="{{ old('seo_meta.focus_keyphrase_' . $locale, $seoMeta->{'focus_keyphrase_' . $locale} ?? '') }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md seo-input-{{ $locale }}"
                   data-locale="{{ $locale }}">
            <p class="mt-1 text-xs text-gray-500">@lang('admin.focus_keyphrase_help')</p>
        </div>

        <!-- Meta Title -->
        <div>
            <label for="meta_title_{{ $locale }}" class="block text-sm font-medium text-gray-700">@lang('admin.seo_title')</label>
            <input type="text" name="seo_meta[meta_title_{{ $locale }}]" id="meta_title_{{ $locale }}"
                   value="{{ old('seo_meta.meta_title_' . $locale, $seoMeta->{'meta_title_' . $locale} ?? '') }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md seo-input-{{ $locale }}"
                   data-locale="{{ $locale }}">
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>@lang('admin.seo_title_help')</span>
                <span id="title-count-{{ $locale }}">0 / 60</span>
            </div>
        </div>

        <!-- Meta Description -->
        <div>
            <label for="meta_description_{{ $locale }}" class="block text-sm font-medium text-gray-700">@lang('admin.meta_description')</label>
            <textarea name="seo_meta[meta_description_{{ $locale }}]" id="meta_description_{{ $locale }}" rows="3"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md seo-input-{{ $locale }}"
                      data-locale="{{ $locale }}">{{ old('seo_meta.meta_description_' . $locale, $seoMeta->{'meta_description_' . $locale} ?? '') }}</textarea>
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>@lang('admin.meta_description_help')</span>
                <span id="desc-count-{{ $locale }}">0 / 160</span>
            </div>
        </div>

        <!-- Analysis Results -->
        <div class="mt-4 p-4 bg-gray-50 border rounded">
             <h4 class="text-sm font-bold text-gray-700 mb-2">@lang('admin.seo_analysis')</h4>
             <ul class="space-y-2 text-sm" id="seo-analysis-{{ $locale }}">
                 <!-- JS will populate this -->
                 <li class="flex items-center">
                     <span class="w-3 h-3 rounded-full bg-gray-300 mr-2 rtl:ml-2 rtl:mr-0 indicator-title"></span>
                     <span>@lang('admin.seo_rule_title_length')</span>
                 </li>
                 <li class="flex items-center">
                     <span class="w-3 h-3 rounded-full bg-gray-300 mr-2 rtl:ml-2 rtl:mr-0 indicator-desc"></span>
                     <span>@lang('admin.seo_rule_desc_length')</span>
                 </li>
                 <li class="flex items-center">
                     <span class="w-3 h-3 rounded-full bg-gray-300 mr-2 rtl:ml-2 rtl:mr-0 indicator-keyphrase"></span>
                     <span>@lang('admin.seo_rule_keyphrase')</span>
                 </li>
             </ul>
        </div>
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const locales = ['en', 'ar'];

        locales.forEach(locale => {
            const inputs = document.querySelectorAll(`.seo-input-${locale}`);

            inputs.forEach(input => {
                input.addEventListener('input', () => analyzeSeo(locale));
            });

            // Initial analysis
            analyzeSeo(locale);
        });
    });

    function analyzeSeo(locale) {
        const titleInput = document.getElementById(`meta_title_${locale}`);
        const descInput = document.getElementById(`meta_description_${locale}`);
        const keyphraseInput = document.getElementById(`focus_keyphrase_${locale}`);

        if (!titleInput || !descInput || !keyphraseInput) return;

        const title = titleInput.value.trim();
        const desc = descInput.value.trim();
        const keyphrase = keyphraseInput.value.trim().toLowerCase();

        // Update counts
        document.getElementById(`title-count-${locale}`).innerText = `${title.length} / 60`;
        document.getElementById(`desc-count-${locale}`).innerText = `${desc.length} / 160`;

        const analysisList = document.getElementById(`seo-analysis-${locale}`);
        const titleIndicator = analysisList.querySelector('.indicator-title');
        const descIndicator = analysisList.querySelector('.indicator-desc');
        const keyphraseIndicator = analysisList.querySelector('.indicator-keyphrase');

        // Rule 1: Title Length
        // Red: < 30 or > 65
        // Orange: 30-39 or 61-65
        // Green: 40-60
        updateIndicator(titleIndicator, title.length, 40, 60, 30, 65);

        // Rule 2: Description Length
        // Red: < 80 or > 180
        // Orange: 80-119 or 161-180
        // Green: 120-160
        updateIndicator(descIndicator, desc.length, 120, 160, 80, 180);

        // Rule 3: Keyphrase Usage
        // Green: In Title AND Description
        // Orange: In Title OR Description
        // Red: Neither (or empty keyphrase)
        if (!keyphrase) {
             setIndicatorColor(keyphraseIndicator, 'gray');
        } else {
            const inTitle = title.toLowerCase().includes(keyphrase);
            const inDesc = desc.toLowerCase().includes(keyphrase);

            if (inTitle && inDesc) {
                setIndicatorColor(keyphraseIndicator, 'green');
            } else if (inTitle || inDesc) {
                setIndicatorColor(keyphraseIndicator, 'orange');
            } else {
                setIndicatorColor(keyphraseIndicator, 'red');
            }
        }
    }

    function updateIndicator(element, length, minGood, maxGood, minOk, maxOk) {
        if (length === 0) {
             setIndicatorColor(element, 'gray');
             return;
        }
        if (length >= minGood && length <= maxGood) {
            setIndicatorColor(element, 'green');
        } else if (length >= minOk && length <= maxOk) {
            setIndicatorColor(element, 'orange');
        } else {
            setIndicatorColor(element, 'red');
        }
    }

    function setIndicatorColor(element, color) {
        element.classList.remove('bg-gray-300', 'bg-red-500', 'bg-yellow-500', 'bg-green-500');
        if (color === 'gray') element.classList.add('bg-gray-300');
        if (color === 'red') element.classList.add('bg-red-500');
        if (color === 'orange') element.classList.add('bg-yellow-500'); // Orange often represented as yellow-500 in Tailwind default
        if (color === 'green') element.classList.add('bg-green-500');
    }
</script>
@endonce
