<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitType::with('propertyType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
        }

        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }

        $unitTypes = $query->paginate(10);
        $propertyTypes = PropertyType::all(); // For filter

        return view('admin.unit_types.index', compact('unitTypes', 'propertyTypes'));
    }

    public function create()
    {
        $propertyTypes = PropertyType::all();
        return view('admin.unit_types.create', compact('propertyTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_type_id' => 'required|exists:property_types,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon_class' => 'nullable|string|max:120',
            'image_url' => 'nullable|url|max:255',
            // Booleans are checked below
        ]);

        $booleans = [
            'is_active',
            'requires_land_area',
            'requires_built_up_area',
            'requires_garden_area',
            'requires_roof_area',
            'requires_indoor_area',
            'requires_outdoor_area'
        ];

        foreach ($booleans as $field) {
            $validated[$field] = $request->has($field);
        }

        UnitType::create($validated);

        return redirect()->route('admin.unit-types.index')
            ->with('success', __('admin.save') . ' ' . __('admin.unit_types'));
    }

    public function edit(UnitType $unitType)
    {
        $propertyTypes = PropertyType::all();
        return view('admin.unit_types.edit', compact('unitType', 'propertyTypes'));
    }

    public function update(Request $request, UnitType $unitType)
    {
        $validated = $request->validate([
            'property_type_id' => 'required|exists:property_types,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon_class' => 'nullable|string|max:120',
            'image_url' => 'nullable|url|max:255',
        ]);

        $booleans = [
            'is_active',
            'requires_land_area',
            'requires_built_up_area',
            'requires_garden_area',
            'requires_roof_area',
            'requires_indoor_area',
            'requires_outdoor_area'
        ];

        foreach ($booleans as $field) {
            $validated[$field] = $request->has($field);
        }

        $unitType->update($validated);

        return redirect()->route('admin.unit-types.index')
            ->with('success', __('admin.save') . ' ' . __('admin.unit_types'));
    }

    public function destroy(UnitType $unitType)
    {
        $unitType->delete();

        return redirect()->route('admin.unit-types.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.unit_types'));
    }
}
