<?php

namespace App\Http\Requests\Admin\PropertyModels;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\UnitType;

class UpdatePropertyModelRequest extends FormRequest
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
        $id = $this->route('property_model') ? $this->route('property_model')->id : null;

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
            'seo_slug_en' => 'nullable|string|unique:property_models,seo_slug_en,' . $id,
            'seo_slug_ar' => 'nullable|string|unique:property_models,seo_slug_ar,' . $id,
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
