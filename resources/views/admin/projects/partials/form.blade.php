@php
    $project = $project ?? new \App\Models\Project();
    $isEdit = $project->exists;
    $adminRoutePrefix = $adminRoutePrefix ?? 'admin.';
    $existingGalleryImages = old('existing_gallery_images', $isEdit ? ($project->gallery_images ?? []) : []);
    $videoUrls = old('video_urls', $isEdit ? ($project->video_urls ?? []) : []);
    $faqItems = old('faqs', $isEdit ? $project->faqs->map(fn ($faq) => [
        'id' => $faq->id,
        'question_ar' => $faq->question_ar,
        'answer_ar' => $faq->answer_ar,
        'question_en' => $faq->question_en,
        'answer_en' => $faq->answer_en,
    ])->toArray() : []);
    $paymentProfiles = old('payment_profiles', $isEdit ? ($project->payment_profiles ?? []) : []);
    $phases = old('phases', $isEdit ? ($project->phases ?? []) : []);
    $rawPartOfMaster = old('is_part_of_master_project', $project->is_part_of_master_project);
    $normalizedPartOfMaster = isset($rawPartOfMaster) && $rawPartOfMaster !== ''
        ? ((string) $rawPartOfMaster === '1' ? '1' : '0')
        : '';
@endphp

<div x-data="projectWizard({
        initialStep: 1,
        videoUrls: @json($videoUrls),
        faqs: @json($faqItems),
        paymentProfiles: @json($paymentProfiles),
        phases: @json($phases),
        existingGallery: @json($existingGalleryImages)
    })"
    class="bg-white shadow rounded-lg p-6 space-y-6">

    <div class="border-b border-gray-200">
        <nav class="-mb-px flex flex-wrap" aria-label="Tabs">
            <template x-for="(step, index) in steps" :key="index">
                <button type="button"
                        class="whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm"
                        :class="currentStep === step.id ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        x-text="step.label"
                        @click="goToStep(step.id)"></button>
            </template>
        </nav>
    </div>

    <form action="{{ $isEdit ? route($adminRoutePrefix.'projects.update', $project->id) : route($adminRoutePrefix.'projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @if($isEdit) @method('PUT') @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">{{ __('admin.correct_errors') }}</strong>
                <ul class="mt-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Step 1: Basic Info & Location --}}
        <div x-show="currentStep === 1" x-cloak class="space-y-6">
            @include('admin.projects.steps.step1_basic_location')
        </div>

        {{-- Step 2: Marketing Content --}}
        <div x-show="currentStep === 2" x-cloak class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800">{{ __('admin.projects.steps.marketing') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.project_title_ar') }}</label>
                    <input type="text" name="project_title_ar" value="{{ old('project_title_ar', $project->title_ar ?? '') }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.project_title_en') }}</label>
                    <input type="text" name="project_title_en" value="{{ old('project_title_en', $project->title_en ?? '') }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.description_short_ar') }}</label>
                    <textarea name="description_short_ar" rows="2" class="w-full rounded border-gray-300">{{ old('description_short_ar', $project->description_short_ar ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.description_short_en') }}</label>
                    <textarea name="description_short_en" rows="2" class="w-full rounded border-gray-300">{{ old('description_short_en', $project->description_short_en ?? '') }}</textarea>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.description_ar') }}</label>
                    <textarea name="description_ar" rows="4" class="w-full rounded border-gray-300">{{ old('description_ar', $project->description_ar ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.description_en') }}</label>
                    <textarea name="description_en" rows="4" class="w-full rounded border-gray-300">{{ old('description_en', $project->description_en ?? '') }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.amenities') }}</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                    @foreach($amenities as $group => $items)
                        <div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">{{ ucfirst($group) }}</p>
                            <div class="space-y-1">
                                @foreach($items as $amenity)
                                    <label class="inline-flex items-center space-x-2">
                                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" {{ in_array($amenity->id, old('amenity_ids', $project->amenities?->pluck('id')->toArray() ?? [])) ? 'checked' : '' }} />
                                        <span>{{ $amenity->name_en ?? $amenity->name_ar ?? $amenity->name }}</span>
                                    </label><br>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-3" x-data="{ faqs: faqs }">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800">{{ __('admin.projects.faq') }}</h4>
                    <button type="button" @click="faqs.push({question_ar:'', answer_ar:'', question_en:'', answer_en:''})" class="px-3 py-1 bg-indigo-600 text-white rounded">{{ __('admin.add') }}</button>
                </div>
                <template x-for="(faq, index) in faqs" :key="index">
                    <div class="border rounded p-3 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <input type="hidden" :name="`faqs[${index}][id]`" :value="faq.id">
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.question_ar') }}</label>
                                <input type="text" class="w-full rounded border-gray-300" :name="`faqs[${index}][question_ar]`" x-model="faq.question_ar">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.question_en') }}</label>
                                <input type="text" class="w-full rounded border-gray-300" :name="`faqs[${index}][question_en]`" x-model="faq.question_en">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.answer_ar') }}</label>
                                <textarea class="w-full rounded border-gray-300" rows="2" :name="`faqs[${index}][answer_ar]`" x-model="faq.answer_ar"></textarea>
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.answer_en') }}</label>
                                <textarea class="w-full rounded border-gray-300" rows="2" :name="`faqs[${index}][answer_en]`" x-model="faq.answer_en"></textarea>
                            </div>
                        </div>
                        <button type="button" class="text-red-600 text-sm" @click="faqs.splice(index, 1)">{{ __('admin.delete') }}</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Step 3: Media & Gallery --}}
        <div x-show="currentStep === 3" x-cloak class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800">{{ __('admin.projects.steps.media') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.gallery_images') }}</label>
                    <input type="file" name="gallery_images[]" multiple accept="image/*" class="w-full" />
                    @if($isEdit && $existingGalleryImages)
                        <div class="mt-3 space-y-2">
                            <p class="text-sm text-gray-700 font-semibold">{{ __('admin.projects.current_gallery') }}</p>
                            @foreach($existingGalleryImages as $idx => $image)
                                <div class="flex items-center space-x-3">
                                    <img src="{{ Storage::disk('public')->url($image) }}" class="h-12 w-12 object-cover rounded" alt="gallery">
                                    <input type="hidden" name="existing_gallery_images[]" value="{{ $image }}">
                                    <span class="text-xs text-gray-600 break-all">{{ $image }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.master_plan_image') }}</label>
                    <input type="file" name="master_plan_image" accept="image/*" class="w-full" />
                    @if($isEdit && $project->master_plan_image)
                        <p class="text-xs text-gray-600 mt-2">{{ __('admin.projects.current_file') }}: {{ $project->master_plan_image }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.brochure_file') }}</label>
                    <input type="file" name="brochure_file" accept="application/pdf" class="w-full" />
                    @if($isEdit && $project->brochure_file_path)
                        <p class="text-xs text-gray-600 mt-2">{{ __('admin.projects.current_file') }}: {{ $project->brochure_file_path }}</p>
                    @endif
                </div>
            </div>

            <div class="space-y-3" x-data="{ videos: videoUrls }">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800">{{ __('admin.projects.video_links') }}</h4>
                    <button type="button" @click="videos.push('')" class="px-3 py-1 bg-indigo-600 text-white rounded">{{ __('admin.add') }}</button>
                </div>
                <template x-for="(video, index) in videos" :key="index">
                    <div class="flex items-center space-x-2">
                        <input type="url" class="w-full rounded border-gray-300" :name="`video_urls[${index}]`" x-model="videos[index]" placeholder="https://">
                        <button type="button" class="text-red-600 text-sm" @click="videos.splice(index, 1)">{{ __('admin.delete') }}</button>
                    </div>
                </template>
            </div>

        </div>

        {{-- Step 4: Financials --}}
        <div x-show="currentStep === 4" x-cloak class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800">{{ __('admin.projects.steps.financials') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.financial_summary_ar') }}</label>
                    <textarea name="financial_summary_ar" rows="3" class="w-full rounded border-gray-300">{{ old('financial_summary_ar', $project->financial_summary_ar ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.financial_summary_en') }}</label>
                    <textarea name="financial_summary_en" rows="3" class="w-full rounded border-gray-300">{{ old('financial_summary_en', $project->financial_summary_en ?? '') }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.min_price') }}</label>
                    <input type="number" step="0.01" name="min_price" value="{{ old('min_price', $project->min_price ?? '') }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.max_price') }}</label>
                    <input type="number" step="0.01" name="max_price" value="{{ old('max_price', $project->max_price ?? '') }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.min_bua') }}</label>
                    <input type="number" step="0.01" name="min_bua" value="{{ old('min_bua', $project->min_bua ?? '') }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.max_bua') }}</label>
                    <input type="number" step="0.01" name="max_bua" value="{{ old('max_bua', $project->max_bua ?? '') }}" class="w-full rounded border-gray-300" />
                </div>
            </div>

            <div class="space-y-3" x-data="{ profiles: paymentProfiles }">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800">{{ __('admin.projects.payment_profiles') }}</h4>
                    <button type="button" @click="profiles.push({name:'', down_payment_percent:'', years:'', installment_frequency:'', notes:''})" class="px-3 py-1 bg-indigo-600 text-white rounded">{{ __('admin.add') }}</button>
                </div>
                <template x-for="(profile, index) in profiles" :key="index">
                    <div class="border rounded p-3 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.payment_profile_name') }}</label>
                                <input type="text" class="w-full rounded border-gray-300" :name="`payment_profiles[${index}][name]`" x-model="profile.name">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.down_payment_percent') }}</label>
                                <input type="number" step="0.01" class="w-full rounded border-gray-300" :name="`payment_profiles[${index}][down_payment_percent]`" x-model="profile.down_payment_percent">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.years') }}</label>
                                <input type="number" step="0.1" class="w-full rounded border-gray-300" :name="`payment_profiles[${index}][years]`" x-model="profile.years">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.installment_frequency') }}</label>
                                <input type="text" class="w-full rounded border-gray-300" :name="`payment_profiles[${index}][installment_frequency]`" x-model="profile.installment_frequency">
                            </div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-700">{{ __('admin.projects.notes') }}</label>
                            <textarea class="w-full rounded border-gray-300" rows="2" :name="`payment_profiles[${index}][notes]`" x-model="profile.notes"></textarea>
                        </div>
                        <button type="button" class="text-red-600 text-sm" @click="profiles.splice(index, 1)">{{ __('admin.delete') }}</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Step 5: Models & Phases --}}
        <div x-show="currentStep === 5" x-cloak class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800">{{ __('admin.projects.steps.models_phases') }}</h3>
            <div class="border rounded p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-gray-800">{{ __('admin.projects.property_models') }}</h4>
                    @if($isEdit)
                        <a href="{{ route($adminRoutePrefix.'property-models.index', ['project_id' => $project->id]) }}" class="px-3 py-1 bg-indigo-600 text-white rounded">{{ __('admin.projects.manage_models') }}</a>
                    @else
                        <p class="text-sm text-gray-600">{{ __('admin.projects.save_first_for_models') }}</p>
                    @endif
                </div>
                @if($isEdit && $project->propertyModels->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="px-3 py-2">{{ __('admin.name') }}</th>
                                    <th class="px-3 py-2">{{ __('admin.unit_type') }}</th>
                                    <th class="px-3 py-2">{{ __('admin.projects.min_price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->propertyModels as $model)
                                    <tr class="border-b">
                                        <td class="px-3 py-2">{{ $model->name }}</td>
                                        <td class="px-3 py-2">{{ $model->unitType->name ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $model->min_price ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-y-3" x-data="{ phases: phases }">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800">{{ __('admin.projects.phases') }}</h4>
                    <button type="button" @click="phases.push({name:'', delivery_year:'', status:'', notes:''})" class="px-3 py-1 bg-indigo-600 text-white rounded">{{ __('admin.add') }}</button>
                </div>
                <template x-for="(phase, index) in phases" :key="index">
                    <div class="border rounded p-3 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.phase_name') }}</label>
                                <input type="text" class="w-full rounded border-gray-300" :name="`phases[${index}][name]`" x-model="phase.name">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.delivery_year') }}</label>
                                <input type="number" class="w-full rounded border-gray-300" :name="`phases[${index}][delivery_year]`" x-model="phase.delivery_year">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">{{ __('admin.projects.phase_status') }}</label>
                                <input type="text" class="w-full rounded border-gray-300" :name="`phases[${index}][status]`" x-model="phase.status">
                            </div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-700">{{ __('admin.projects.notes') }}</label>
                            <textarea class="w-full rounded border-gray-300" rows="2" :name="`phases[${index}][notes]`" x-model="phase.notes"></textarea>
                        </div>
                        <button type="button" class="text-red-600 text-sm" @click="phases.splice(index, 1)">{{ __('admin.delete') }}</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Step 6: Settings & Publishing --}}
        <div x-show="currentStep === 6" x-cloak class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800">{{ __('admin.projects.steps.settings') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $project->is_featured ?? false) ? 'checked' : '' }} />
                    <span>{{ __('admin.projects.is_featured') }}</span>
                </label>
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="is_top_project" value="1" {{ old('is_top_project', $project->is_top_project ?? false) ? 'checked' : '' }} />
                    <span>{{ __('admin.projects.is_top_project') }}</span>
                </label>
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $project->is_active ?? true) ? 'checked' : '' }} />
                    <span>{{ __('admin.projects.is_active') }}</span>
                </label>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.publish_status') }}</label>
                    <select name="status" class="w-full rounded border-gray-300">
                        <option value="draft" {{ old('status', $project->status ?? 'draft') === 'draft' ? 'selected' : '' }}>{{ __('admin.projects.draft') }}</option>
                        <option value="published" {{ old('status', $project->status ?? '') === 'published' ? 'selected' : '' }}>{{ __('admin.projects.published') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ __('admin.projects.published_at') }}</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($project->published_at ?? null)->format('Y-m-d\TH:i')) }}" class="w-full rounded border-gray-300" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t">
            <button type="button" class="px-4 py-2 rounded bg-gray-200 text-gray-700" @click="previousStep" x-show="currentStep > 1">{{ __('admin.previous') }}</button>
            <div class="flex space-x-2">
                <button type="button" class="px-4 py-2 rounded bg-indigo-600 text-white" @click="nextStep" x-show="currentStep < steps.length">{{ __('admin.next') }}</button>
                <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white" x-show="currentStep === steps.length">{{ __('admin.save') }}</button>
            </div>
        </div>
    </form>
</div>

<script>
    function projectWizard(config) {
        return {
            currentStep: config.initialStep || 1,
            steps: [
                { id: 1, label: @json(__('admin.projects.steps.basic')) },
                { id: 2, label: @json(__('admin.projects.steps.marketing')) },
                { id: 3, label: @json(__('admin.projects.steps.media')) },
                { id: 4, label: @json(__('admin.projects.steps.financials')) },
                { id: 5, label: @json(__('admin.projects.steps.models_phases')) },
                { id: 6, label: @json(__('admin.projects.steps.settings')) },
            ],
            step1Errors: [],
            partOfMaster: '{{ $normalizedPartOfMaster }}',
            videoUrls: config.videoUrls && config.videoUrls.length ? config.videoUrls : [''],
            faqs: config.faqs && config.faqs.length ? config.faqs : [{question_ar:'',answer_ar:'',question_en:'',answer_en:''}],
            paymentProfiles: config.paymentProfiles && config.paymentProfiles.length ? config.paymentProfiles : [{name:'',down_payment_percent:'',years:'',installment_frequency:'',notes:''}],
            phases: config.phases && config.phases.length ? config.phases : [{name:'',delivery_year:'',status:'',notes:''}],
            goToStep(step) {
                if (step > this.currentStep && this.currentStep === 1 && !this.validateStep1()) {
                    return;
                }
                this.currentStep = step;
                this.refreshMap();
            },
            nextStep() {
                if (this.currentStep === 1 && !this.validateStep1()) return;
                if (this.currentStep < this.steps.length) {
                    this.currentStep++;
                    this.refreshMap();
                }
            },
            previousStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    this.refreshMap();
                }
            },
            validateStep1() {
                this.step1Errors = [];
                const nameAr = document.querySelector('input[name="name_ar"]')?.value.trim();
                const nameEn = document.querySelector('input[name="name_en"]')?.value.trim();
                const developer = document.querySelector('select[name="developer_id"]')?.value;
                const partOfMaster = document.getElementById('is_part_of_master_project')?.value;
                const masterProject = document.querySelector('select[name="master_project_id"]')?.value;
                const region = document.getElementById('region_id')?.value;
                const city = document.getElementById('city_id')?.value;

                if (!nameAr) this.step1Errors.push(@json(__('admin.projects.name_ar')).concat(' ', 'is required'));
                if (!nameEn) this.step1Errors.push(@json(__('admin.projects.name_en')).concat(' ', 'is required'));
                if (!developer) this.step1Errors.push(@json(__('admin.projects.developer')).concat(' ', 'is required'));
                if (partOfMaster === undefined || partOfMaster === '') {
                    this.step1Errors.push(@json(__('admin.projects.project_type_required')));
                }

                if (partOfMaster === '1') {
                    if (!masterProject) {
                        this.step1Errors.push(@json(__('admin.projects.master_project')).concat(' ', 'is required when project is a phase.'));
                    }
                }

                if (!region) this.step1Errors.push(@json(__('admin.projects.region')).concat(' ', 'is required'));
                if (!city) this.step1Errors.push(@json(__('admin.projects.city')).concat(' ', 'is required'));

                return this.step1Errors.length === 0;
            },
            refreshMap() {
                this.$nextTick(() => {
                    if (this.currentStep === 1 && window['map_project-map']) {
                        window['map_project-map'].invalidateSize();
                        const zoomInput = document.getElementById('map_zoom');
                        if (zoomInput) {
                            window['map_project-map'].on('zoomend', () => {
                                zoomInput.value = window['map_project-map'].getZoom();
                            });
                        }
                    }
                });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const regionSelect = document.getElementById('region_id');
        const citySelect = document.getElementById('city_id');
        const districtSelect = document.getElementById('district_id');
        const locationProjectSelect = document.getElementById('location_project_id');
        const masterProjectSelect = document.getElementById('master_project_id');
        const partOfMasterSelect = document.getElementById('is_part_of_master_project');
        const locationInheritBadge = document.getElementById('location_inherit_badge');
        const locationInputs = document.querySelectorAll('[data-location-select]');
        const locationBlock = document.getElementById('project-location-block');

        const adminPrefix = (() => {
            const parts = window.location.pathname.split('/').filter(Boolean);
            if (parts[0] && parts[0].length === 2 && parts[1] === 'admin') {
                return `/${parts[0]}/admin`;
            }
            return '/admin';
        })();

        const buildAdminUrl = (path) => {
            const normalized = path.startsWith('/') ? path : `/${path}`;
            return `${adminPrefix}${normalized.replace(/^\/admin/, '')}`;
        };

        const presetRegion = '{{ old('region_id', $project->region_id ?? '') }}';
        const presetCity = '{{ old('city_id', $project->city_id ?? '') }}';
        const presetDistrict = '{{ old('district_id', $project->district_id ?? '') }}';
        const presetLocationProject = '{{ old('location_project_id', $project->location_project_id ?? '') }}';
        const currentProjectId = '{{ $project->id ?? '' }}';
        const defaultCountryId = document.getElementById('country_id')?.value || '{{ $defaultCountryId ?? '' }}';

        function clearSelect(select, placeholder, disable = false) {
            if (!select) return;
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = disable;
        }

        function populateSelect(select, data, selectedValue, placeholder, allowEmptyOption = true) {
            if (!select) return;
            select.innerHTML = allowEmptyOption ? `<option value="">${placeholder}</option>` : '';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.name_en || item.name_local || item.name;
                if (item.lat) option.dataset.lat = item.lat;
                if (item.lng) option.dataset.lng = item.lng;
                if (item.map_lat) option.dataset.lat = item.map_lat;
                if (item.map_lng) option.dataset.lng = item.map_lng;
                if (item.map_zoom) option.dataset.mapZoom = item.map_zoom;
                if (selectedValue && String(selectedValue) === String(item.id)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        async function fetchOptions(url, preferredKey) {
            const response = await fetch(url);
            if (!response.ok) return [];
            const payload = await response.json();
            if (Array.isArray(payload)) return payload;
            if (preferredKey && Array.isArray(payload[preferredKey])) return payload[preferredKey];
            const firstArray = Object.values(payload).find(Array.isArray);
            return firstArray || [];
        }

        async function loadRegions(selected) {
            const countryId = defaultCountryId;
            if (!countryId) {
                clearSelect(regionSelect, '{{ __('admin.select_region') }}');
                clearSelect(citySelect, '{{ __('admin.select_city') }}');
                clearSelect(districtSelect, '{{ __('admin.select_district') }}');
                clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
                return;
            }
            const data = await fetchOptions(buildAdminUrl(`/locations/countries/${countryId}`), 'regions');
            populateSelect(regionSelect, data, selected, '{{ __('admin.select_region') }}');
        }

        async function loadCities(regionId, selected) {
            if (!regionId) {
                clearSelect(citySelect, '{{ __('admin.select_city') }}');
                clearSelect(districtSelect, '{{ __('admin.select_district') }}');
                clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
                return;
            }
            const data = await fetchOptions(buildAdminUrl(`/locations/regions/${regionId}`), 'cities');
            populateSelect(citySelect, data, selected, '{{ __('admin.select_city') }}');
        }

        async function loadDistricts(cityId, selected) {
            if (!cityId) {
                clearSelect(districtSelect, '{{ __('admin.select_district') }}');
                clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
                return;
            }
            const data = await fetchOptions(buildAdminUrl(`/locations/cities/${cityId}`), 'districts');
            populateSelect(districtSelect, data, selected, '{{ __('admin.select_district') }}');
        }

        async function loadLocationProjects(districtId, selected) {
            if (!districtId) {
                clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
                return;
            }
            const projects = await fetchOptions(buildAdminUrl(`/locations/districts/${districtId}/projects`), 'projects');
            const filteredProjects = projects.filter(item => !currentProjectId || String(item.id) !== String(currentProjectId));
            if (!filteredProjects.length) {
                clearSelect(locationProjectSelect, '{{ __('admin.projects.no_linked_projects') }}', false);
                locationProjectSelect.disabled = false;
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '{{ __('admin.projects.no_linked_projects') }}';
                emptyOption.disabled = true;
                emptyOption.selected = true;
                locationProjectSelect.appendChild(emptyOption);
                return;
            }
            populateSelect(locationProjectSelect, filteredProjects, selected, '{{ __('admin.projects.select_location_project') }}');
            locationProjectSelect.disabled = false;
        }

        function toggleLocationLock(lock) {
            locationInputs.forEach(select => {
                if (lock) {
                    select.classList.add('bg-gray-100', 'cursor-not-allowed');
                    select.style.pointerEvents = 'none';
                    select.tabIndex = -1;
                } else {
                    select.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    select.style.pointerEvents = '';
                    select.tabIndex = 0;
                }
            });
            if (locationInheritBadge) {
                locationInheritBadge.hidden = !lock;
            }
        }

        function refreshMapSize() {
            if (window['map_project-map']) {
                window['map_project-map'].invalidateSize();
            }
        }

        function showLocationBlock() {
            if (locationBlock) {
                locationBlock.style.display = 'block';
                setTimeout(refreshMapSize, 150);
            }
        }

        function hideLocationBlock() {
            if (locationBlock) {
                locationBlock.style.display = 'none';
            }
        }

        function setProjectType(value) {
            if (!partOfMasterSelect || partOfMasterSelect.value === value) return;
            partOfMasterSelect.value = value;
            partOfMasterSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function syncLocationVisibility() {
            const selected = partOfMasterSelect?.value;
            if (selected === '0') {
                toggleLocationLock(false);
                showLocationBlock();
            } else if (selected === '1') {
                showLocationBlock();
                toggleLocationLock(true);
                if (!masterProjectSelect?.value) {
                    clearSelect(regionSelect, '{{ __('admin.select_region') }}');
                    clearSelect(citySelect, '{{ __('admin.select_city') }}');
                    clearSelect(districtSelect, '{{ __('admin.select_district') }}');
                    clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}');
                }
            } else {
                hideLocationBlock();
                toggleLocationLock(false);
            }
        }

        function updateCoordinateInputs(lat, lng) {
            const latInput = document.getElementById('map_lat');
            const lngInput = document.getElementById('map_lng');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;
        }

        function flyMapTo(lat, lng, zoom = 14) {
            if (!lat || !lng) return;
            const map = window['map_project-map'];
            showLocationBlock();
            if (map) {
                if (window.projectMapMarker) {
                    map.removeLayer(window.projectMapMarker);
                }
                window.projectMapMarker = L.marker([lat, lng]).addTo(map);
                map.flyTo([lat, lng], zoom);
                const zoomInput = document.getElementById('map_zoom');
                if (zoomInput) {
                    zoomInput.value = map.getZoom();
                }
            }
            if (window.unifiedMap && typeof window.unifiedMap.flyToLocation === 'function') {
                window.unifiedMap.flyToLocation({ lat, lng, zoom });
            }
            updateCoordinateInputs(lat, lng);
        }

        function flyToSelectedOption(select, zoom = 12) {
            const option = select?.selectedOptions?.[0];
            const lat = option?.dataset?.lat;
            const lng = option?.dataset?.lng;
            if (lat && lng) {
                flyMapTo(parseFloat(lat), parseFloat(lng), zoom);
            }
        }

        let highlightedPolygonLayer = null;
        let polygonsDataPromise = null;

        function clearHighlightedPolygon() {
            const map = window['map_project-map'];
            if (highlightedPolygonLayer && map) {
                map.removeLayer(highlightedPolygonLayer);
            }
            highlightedPolygonLayer = null;
        }

        function parsePolygon(polygon) {
            if (!polygon) return null;
            if (typeof polygon === 'string') {
                try {
                    return JSON.parse(polygon);
                } catch (e) {
                    return null;
                }
            }
            return polygon;
        }

        async function loadPolygonData() {
            if (!polygonsDataPromise) {
                polygonsDataPromise = fetch(buildAdminUrl('/location-polygons'))
                    .then(res => res.ok ? res.json() : {})
                    .catch(() => ({}));
            }
            return polygonsDataPromise;
        }

        async function focusOnSelection(level, id, selectEl, fallbackZoom = 12) {
            if (!id) {
                clearHighlightedPolygon();
                return;
            }
            const map = window['map_project-map'];
            showLocationBlock();
            const polygons = await loadPolygonData();
            const keyMap = { country: 'countries', region: 'regions', city: 'cities', district: 'districts', project: 'projects' };
            const collection = polygons?.[keyMap[level]] || [];
            const target = collection.find(item => String(item.id) === String(id));
            const polygonData = parsePolygon(target?.polygon);
            if (polygonData && map) {
                if (highlightedPolygonLayer) {
                    map.removeLayer(highlightedPolygonLayer);
                }
                highlightedPolygonLayer = L.geoJSON(polygonData, {
                    style: { color: '#2563eb', weight: 2, fillOpacity: 0.12 }
                }).addTo(map);
                if (highlightedPolygonLayer.getBounds().isValid()) {
                    map.fitBounds(highlightedPolygonLayer.getBounds(), { padding: [20, 20] });
                    const center = highlightedPolygonLayer.getBounds().getCenter();
                    updateCoordinateInputs(center.lat, center.lng);
                    const zoomInput = document.getElementById('map_zoom');
                    if (zoomInput) zoomInput.value = map.getZoom();
                }
                return;
            }
            const option = selectEl?.selectedOptions?.[0];
            const lat = option?.dataset?.lat;
            const lng = option?.dataset?.lng;
            const zoom = option?.dataset?.mapZoom || fallbackZoom;
            if (lat && lng) {
                flyMapTo(parseFloat(lat), parseFloat(lng), Number(zoom) || fallbackZoom);
            }
        }

        async function applyLocationFromMaster(option) {
            setProjectType('1');
            if (!option || !option.value) {
                toggleLocationLock(true);
                clearSelect(regionSelect, '{{ __('admin.select_region') }}');
                clearSelect(citySelect, '{{ __('admin.select_city') }}');
                clearSelect(districtSelect, '{{ __('admin.select_district') }}');
                clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}');
                return;
            }
            const regionId = option.dataset.region;
            const cityId = option.dataset.city;
            const districtId = option.dataset.district;

            const countryInput = document.getElementById('country_id');
            if (countryInput) {
                countryInput.value = option.dataset.country || defaultCountryId || '';
            }

            await loadRegions(regionId);
            if (regionId) {
                await loadCities(regionId, cityId);
                if (cityId) {
                    await loadDistricts(cityId, districtId);
                    if (districtId) {
                        await loadLocationProjects(districtId, null);
                    }
                }
            }

            toggleLocationLock(true);
            const lat = option.dataset.lat;
            const lng = option.dataset.lng;
            if (lat && lng) {
                flyMapTo(parseFloat(lat), parseFloat(lng));
            }
            showLocationBlock();
        }

        regionSelect?.addEventListener('change', () => {
            clearSelect(citySelect, '{{ __('admin.select_city') }}');
            clearSelect(districtSelect, '{{ __('admin.select_district') }}');
            clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
            loadCities(regionSelect.value);
            focusOnSelection('region', regionSelect.value, regionSelect, 9);
        });

        citySelect?.addEventListener('change', () => {
            clearSelect(districtSelect, '{{ __('admin.select_district') }}');
            clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
            loadDistricts(citySelect.value);
            focusOnSelection('city', citySelect.value, citySelect, 12);
        });

        districtSelect?.addEventListener('change', () => {
            clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
            loadLocationProjects(districtSelect.value);
            focusOnSelection('district', districtSelect.value, districtSelect, 14);
        });

        locationProjectSelect?.addEventListener('change', () => {
            focusOnSelection('project', locationProjectSelect.value, locationProjectSelect, 15);
        });

        partOfMasterSelect?.addEventListener('change', async (event) => {
            syncLocationVisibility();
            if (event.target.value === '1') {
                await applyLocationFromMaster(masterProjectSelect?.selectedOptions[0]);
            }
            if (event.target.value === '0') {
                if (masterProjectSelect) {
                    masterProjectSelect.value = '';
                }
                toggleLocationLock(false);
                showLocationBlock();
            }
        });

        masterProjectSelect?.addEventListener('change', async (event) => {
            const option = event.target.selectedOptions[0];
            const isPart = partOfMasterSelect?.value === '1';
            if (isPart && option?.value) {
                await applyLocationFromMaster(option);
            }
        });

        // initial load
        const loadInitial = async () => {
            if (defaultCountryId) {
                await loadRegions(presetRegion);
                if (presetRegion) {
                    await loadCities(presetRegion, presetCity);
                    if (presetCity) {
                        await loadDistricts(presetCity, presetDistrict);
                        if (presetDistrict) {
                            await loadLocationProjects(presetDistrict, presetLocationProject);
                        }
                    }
                }
            } else {
                clearSelect(regionSelect, '{{ __('admin.select_region') }}');
                clearSelect(citySelect, '{{ __('admin.select_city') }}');
                clearSelect(districtSelect, '{{ __('admin.select_district') }}');
                clearSelect(locationProjectSelect, '{{ __('admin.projects.select_location_project') }}', true);
            }

            const selectedMasterOption = masterProjectSelect?.selectedOptions[0];
            if (partOfMasterSelect?.value === '1' && selectedMasterOption?.value) {
                await applyLocationFromMaster(selectedMasterOption);
            } else {
                syncLocationVisibility();
                if (locationProjectSelect?.value) {
                    focusOnSelection('project', locationProjectSelect.value, locationProjectSelect, 15);
                } else if (districtSelect?.value) {
                    focusOnSelection('district', districtSelect.value, districtSelect, 14);
                } else if (citySelect?.value) {
                    focusOnSelection('city', citySelect.value, citySelect, 12);
                } else if (regionSelect?.value) {
                    focusOnSelection('region', regionSelect.value, regionSelect, 9);
                }
            }
        };

        loadInitial();
    });
</script>
