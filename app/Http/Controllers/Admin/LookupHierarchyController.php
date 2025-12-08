<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LookupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LookupHierarchyController extends Controller
{
    /**
     * Get property types by category.
     *
     * @param Request $request
     * @param LookupService $lookupService
     * @return JsonResponse
     */
    public function propertyTypes(Request $request, LookupService $lookupService): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|integer',
        ]);

        $propertyTypes = $lookupService->getActivePropertyTypesByCategory($request->category_id);

        $data = $propertyTypes->map(function ($propertyType) {
            return [
                'id' => $propertyType->id,
                'name' => $this->getLocalizedName($propertyType),
            ];
        });

        return response()->json($data);
    }

    /**
     * Get unit types by property type.
     *
     * @param Request $request
     * @param LookupService $lookupService
     * @return JsonResponse
     */
    public function unitTypes(Request $request, LookupService $lookupService): JsonResponse
    {
        $request->validate([
            'property_type_id' => 'required|integer',
        ]);

        $unitTypes = $lookupService->getActiveUnitTypesByPropertyType($request->property_type_id);

        $data = $unitTypes->map(function ($unitType) {
            $item = [
                'id' => $unitType->id,
                'name' => $this->getLocalizedName($unitType),
            ];

            // Add optional requirement fields if they exist
            $optionalFields = [
                'requires_land_area',
                'requires_built_up_area',
                'requires_garden_area',
                'requires_roof_area',
                'requires_indoor_area',
                'requires_outdoor_area',
            ];

            foreach ($optionalFields as $field) {
                if (isset($unitType->$field)) {
                    $item[$field] = $unitType->$field;
                }
            }

            return $item;
        });

        return response()->json($data);
    }

    /**
     * Helper to get localized name safely.
     *
     * @param mixed $model
     * @return string
     */
    private function getLocalizedName($model): string
    {
        $locale = app()->getLocale();

        if (method_exists($model, 'getDisplayNameAttribute')) {
             return $model->display_name;
        }

        if (method_exists($model, 'getName')) {
             return $model->getName($locale);
        }

        return ($locale === 'ar')
            ? ($model->name_local ?? $model->name_en)
            : ($model->name_en ?? $model->name_local);
    }
}
