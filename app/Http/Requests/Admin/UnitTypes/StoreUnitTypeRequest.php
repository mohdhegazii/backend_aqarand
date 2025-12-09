<?php

namespace App\Http\Requests\Admin\UnitTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PropertyType;

class StoreUnitTypeRequest extends FormRequest
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
            'property_type_id' => 'required|exists:property_types,id',
            'name_en' => 'required|string|max:150',
            'name_local' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon_class' => 'nullable|string|max:120',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
            'requires_land_area' => 'sometimes|boolean',
            'requires_garden_area' => 'sometimes|boolean',
            'requires_roof_area' => 'sometimes|boolean',
            'requires_indoor_area' => 'sometimes|boolean',
            'requires_outdoor_area' => 'sometimes|boolean',
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
            if ($this->has('category_id') && $this->input('category_id')) {
                $propertyTypeId = $this->input('property_type_id');
                $categoryId = $this->input('category_id');

                $propertyType = PropertyType::find($propertyTypeId);

                if ($propertyType && $propertyType->category_id != $categoryId) {
                     $validator->errors()->add('property_type_id', __('validation.custom.unit_type_property_type_mismatch'));
                     // Also add to category_id to highlight both if needed
                     $validator->errors()->add('category_id', __('validation.custom.unit_type_category_mismatch'));
                }
            }
        });
    }
}
