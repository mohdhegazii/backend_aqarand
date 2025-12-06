<?php

namespace App\Http\Requests\Admin;

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
        return [
            // Basic Info
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'project_area_value' => 'nullable|numeric|min:0',
            'project_area_unit' => 'nullable|in:feddan,sqm|required_with:project_area_value',
            'sales_launch_date' => 'nullable|date',
            'status' => 'required|in:draft,published',
            'construction_status' => 'nullable|in:planned,under_construction,delivered',

            // Master Project
            'is_part_of_master_project' => 'boolean',
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

            // Location
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'address_text' => 'nullable|string|max:255',

            // Description
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',

            // Details
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_bua' => 'nullable|numeric|min:0',
            'max_bua' => 'nullable|numeric|min:0',
            'delivery_year' => 'nullable|integer|min:2000|max:2100',

            // Developer
            'developer_id' => 'nullable|exists:developers,id',

            // Arrays
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',

            'faqs' => 'nullable|array',
            'faqs.*.question_ar' => 'nullable|string',
            'faqs.*.answer_ar' => 'nullable|string',
            'faqs.*.question_en' => 'nullable|string',
            'faqs.*.answer_en' => 'nullable|string',

            // Media Validation (Add basic rules)
            'hero_image_url' => 'nullable|image|max:10240', // 10MB
            'brochure_url' => 'nullable|file|mimes:pdf|max:10240',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:10240',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_part_of_master_project' => (bool) $this->input('is_part_of_master_project', false),
            'is_featured' => (bool) $this->input('is_featured', false),
            'is_top_project' => (bool) $this->input('is_top_project', false),
            'include_in_sitemap' => (bool) $this->input('include_in_sitemap', false),
        ]);
    }
}
