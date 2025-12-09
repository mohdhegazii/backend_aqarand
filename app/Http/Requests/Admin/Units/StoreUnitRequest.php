<?php

namespace App\Http\Requests\Admin\Units;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\UnitType;

class StoreUnitRequest extends FormRequest
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
            'project_id' => 'nullable|exists:projects,id',
            'property_model_id' => 'nullable|exists:property_models,id',
            'unit_type_id' => 'required|exists:unit_types,id',
            'unit_number' => 'nullable|string|max:100',
            'price' => 'required|numeric',
            'currency_code' => 'required|string|size:3',
            'built_up_area' => 'nullable|numeric',
            'title_en' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'unit_status' => 'required|in:available,reserved,sold,rented',
            'construction_status' => 'nullable|in:new_launch,off_plan,under_construction,ready_to_move,livable',
            'delivery_year' => 'nullable|integer|min:2000|max:2100',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'is_corner' => 'sometimes|boolean',
            'is_furnished' => 'sometimes|boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
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
