<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UnitTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitType::with('propertyType');

        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_local', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }

        $unitTypes = $query->paginate(10);
        $propertyTypes = PropertyType::where('is_active', true)->get();

        return view('admin.unit_types.index', compact('unitTypes', 'propertyTypes'));
    }

    public function create()
    {
        $propertyTypes = PropertyType::where('is_active', true)->get();
        return view('admin.unit_types.create', compact('propertyTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_type_id' => 'required|exists:property_types,id',
            'name_en' => 'required|string|max:150',
            'name_local' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon_class' => 'nullable|string|max:120',
            'image' => 'nullable|image|max:2048',
        ]);

        $booleans = [
            'is_active',
            'requires_land_area',
            // 'requires_built_up_area', // Always true, handled separately
            'requires_garden_area',
            'requires_roof_area',
            'requires_indoor_area',
            'requires_outdoor_area'
        ];

        foreach ($booleans as $field) {
            $validated[$field] = $request->has($field);
        }

        // Force requires_built_up_area to be 1
        $validated['requires_built_up_area'] = true;
        $validated['name'] = $validated['name_en'];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lookups', 'public');
            $validated['image_path'] = $path;
            $validated['image_url'] = Storage::url($path);
        }

        UnitType::create($validated);

        return redirect()->route('admin.unit-types.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(UnitType $unitType)
    {
        try {
            $propertyTypes = PropertyType::where('is_active', true)->get();
            return view('admin.unit_types.edit', compact('unitType', 'propertyTypes'))->render();
        } catch (\Throwable $e) {
            dd('DEBUG CAUGHT ERROR IN UnitTypeController::edit', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        }
    }

    public function update(Request $request, UnitType $unitType)
    {
        $validated = $request->validate([
            'property_type_id' => 'required|exists:property_types,id',
            'name_en' => 'required|string|max:150',
            'name_local' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon_class' => 'nullable|string|max:120',
            'image' => 'nullable|image|max:2048',
        ]);

        $booleans = [
            'is_active',
            'requires_land_area',
            // 'requires_built_up_area', // Always true
            'requires_garden_area',
            'requires_roof_area',
            'requires_indoor_area',
            'requires_outdoor_area'
        ];

        foreach ($booleans as $field) {
            $validated[$field] = $request->has($field);
        }

        $validated['requires_built_up_area'] = true;
        $validated['name'] = $validated['name_en'];

        if ($request->hasFile('image')) {
            if ($unitType->image_path) {
                Storage::disk('public')->delete($unitType->image_path);
            }
            $path = $request->file('image')->store('lookups', 'public');
            $validated['image_path'] = $path;
            $validated['image_url'] = Storage::url($path);
        }

        $unitType->update($validated);

        return redirect()->route('admin.unit-types.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(UnitType $unitType)
    {
        $unitType->update(['is_active' => false]);

        return redirect()->route('admin.unit-types.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:unit_types,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        UnitType::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
