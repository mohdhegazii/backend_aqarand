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

                if (!nameAr) this.step1Errors.push(@json(__('admin.projects.name_ar')).concat(' ', 'is required'));
                if (!nameEn) this.step1Errors.push(@json(__('admin.projects.name_en')).concat(' ', 'is required'));

                return this.step1Errors.length === 0;
            },
            refreshMap() {
                return;
            }
        }
    }
</script>
