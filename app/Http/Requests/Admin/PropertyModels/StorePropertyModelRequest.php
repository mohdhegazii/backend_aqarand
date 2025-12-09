<?php

namespace App\Http\Requests\Admin\PropertyModels;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\UnitType;

class StorePropertyModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'unit_type_id' => 'required|exists:unit_types,id',
            'name_en' => 'required|string|max:200',
            'name_ar' => 'nullable|string|max:200',
            'code' => 'nullable|string|max:50',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'min_bua' => 'nullable|numeric',
            'max_bua' => 'nullable|numeric',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'seo_slug_en' => 'nullable|string|unique:property_models,seo_slug_en',
            'seo_slug_ar' => 'nullable|string|unique:property_models,seo_slug_ar',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $unitTypeId = $this->input('unit_type_id');
            $unitType = UnitType::with('propertyType')->find($unitTypeId);

            if (!$unitType) {
                 return; // Already handled by exists rule
            }

            // Optionally verify that the selected unit type is active (if required by business logic, though prompt says 'Optionally')
            // if (!$unitType->is_active) {
            //      $validator->errors()->add('unit_type_id', __('validation.custom.unit_type_inactive'));
            // }

            // Validate Hierarchy if inputs are provided
            if ($this->filled('property_type_id')) {
                if ($unitType->property_type_id != $this->input('property_type_id')) {
                     $validator->errors()->add('unit_type_id', __('validation.custom.hierarchy_mismatch_unit_property'));
                     $validator->errors()->add('property_type_id', __('validation.custom.hierarchy_mismatch_unit_property'));
                }
            }

            if ($this->filled('category_id') && $unitType->propertyType) {
                 if ($unitType->propertyType->category_id != $this->input('category_id')) {
                      $validator->errors()->add('category_id', __('validation.custom.hierarchy_mismatch_property_category'));
                 }
            }
        });
    }
}
