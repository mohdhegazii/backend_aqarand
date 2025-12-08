<?php

namespace App\Http\Requests\Admin;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $egyptId = $this->defaultCountryId();

        return [
            // Basic Info
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'project_area' => 'nullable|numeric|min:0',
            'project_area_unit' => 'nullable|string|max:50',
            'launch_date' => 'nullable|date',
            'status' => 'required|in:draft,published',
            'construction_status' => 'nullable|in:planned,under_construction,delivered',

            // Master Project
            'is_part_of_master_project' => 'required|in:0,1',
            'master_project_id' => [
                'nullable',
                'required_if:is_part_of_master_project,1',
                'exists:projects,id',
                Rule::notIn([$this->route('project')?->id]),
            ],

            // Flags & Dates
            'is_featured' => 'boolean',
            'is_top_project' => 'boolean',
            'include_in_sitemap' => 'boolean',
            'is_active' => 'boolean',

            // Location
            'country_id' => ['required', Rule::in([$egyptId])],
            'region_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'location_project_id' => [
                'nullable',
                'exists:projects,id',
                Rule::notIn([$this->route('project')?->id]),
            ],
            'map_lat' => 'nullable|numeric',
            'map_lng' => 'nullable|numeric',
            'map_zoom' => 'nullable|integer',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'map_polygon' => 'nullable',
            'address_text' => 'nullable|string|max:255',

            // Description
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_short_ar' => 'nullable|string',
            'description_short_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',

            // Details
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_bua' => 'nullable|numeric|min:0',
            'max_bua' => 'nullable|numeric|min:0',
            'delivery_year' => 'nullable|integer|min:2000|max:2100',

            // Developer
            'developer_id' => 'required|exists:developers,id',

            // Arrays
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',

            'faqs' => 'nullable|array',
            'faqs.*.question_ar' => 'nullable|string',
            'faqs.*.answer_ar' => 'nullable|string',
            'faqs.*.question_en' => 'nullable|string',
            'faqs.*.answer_en' => 'nullable|string',
            'faqs.*.sort_order' => 'nullable|integer',
            'faqs.*.id' => 'nullable|integer|exists:project_faqs,id',

            // Marketing content
            'project_title_ar' => 'nullable|string|max:255',
            'project_title_en' => 'nullable|string|max:255',
            'financial_summary_ar' => 'nullable|string',
            'financial_summary_en' => 'nullable|string',

            // Payment profiles
            'payment_profiles' => 'nullable|array',
            'payment_profiles.*.name' => 'nullable|string|max:255',
            'payment_profiles.*.down_payment_percent' => 'nullable|numeric|min:0',
            'payment_profiles.*.years' => 'nullable|numeric|min:0',
            'payment_profiles.*.installment_frequency' => 'nullable|string|max:255',
            'payment_profiles.*.notes' => 'nullable|string',

            // Phases
            'phases' => 'nullable|array',
            'phases.*.name' => 'nullable|string|max:255',
            'phases.*.delivery_year' => 'nullable|integer|min:2000|max:2100',
            'phases.*.status' => 'nullable|string|max:255',
            'phases.*.notes' => 'nullable|string',

            // Media Validation (Add basic rules)
            'hero_image_url' => 'nullable|image|max:10240', // 10MB
            'brochure_file' => 'nullable|file|mimes:pdf|max:10240',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|max:10240',
            'master_plan_image' => 'nullable|image|max:10240',
            'video_urls' => 'nullable|array',
            'video_urls.*' => 'nullable|url',
        ];
    }

    public function messages()
    {
        return [
            'is_part_of_master_project.required' => __('admin.projects.project_type_required'),
        ];
    }

    public function prepareForValidation()
    {
        $partOfMasterRaw = $this->input('is_part_of_master_project', null);
        $defaultCountryId = $this->defaultCountryId();

        $this->merge([
            'is_part_of_master_project' => $this->normalizeNullableBoolean($partOfMasterRaw),
            'is_featured' => (bool) $this->input('is_featured', false),
            'is_top_project' => (bool) $this->input('is_top_project', false),
            'include_in_sitemap' => (bool) $this->input('include_in_sitemap', false),
            'is_active' => (bool) $this->input('is_active', true),
            'project_area_value' => $this->input('project_area'),
            'sales_launch_date' => $this->input('launch_date'),
            'lat' => $this->input('map_lat'),
            'lng' => $this->input('map_lng'),
            'amenities' => $this->input('amenity_ids', $this->input('amenities', [])),
            'title_ar' => $this->input('project_title_ar'),
            'title_en' => $this->input('project_title_en'),
            'country_id' => $defaultCountryId,
        ]);
    }

    private function defaultCountryId(): ?int
    {
        return Country::where('code', 'EG')->value('id')
            ?? Country::where('name_en', 'Egypt')->value('id')
            ?? Country::value('id');
    }

    private function normalizeNullableBoolean($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return in_array($value, ['1', 1, true, 'true', 'on'], true) ? 1 : 0;
    }
}
