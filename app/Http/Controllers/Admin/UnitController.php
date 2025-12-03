<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Project;
use App\Models\PropertyModel;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::with(['project', 'propertyModel', 'unitType']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $units = $query->latest()->paginate(10);
        $projects = Project::orderBy('name_en')->get();

        return view('admin.units.index', compact('units', 'projects'));
    }

    public function create()
    {
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();
        $propertyModels = PropertyModel::orderBy('name_en')->get(); // Ideally filtered by project via AJAX

        return view('admin.units.create', compact('projects', 'unitTypes', 'propertyModels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
        ]);

        // Auto calculate price per sqm
        $pricePerSqm = null;
        if ($request->built_up_area > 0 && $request->price > 0) {
            $pricePerSqm = $request->price / $request->built_up_area;
        }

        Unit::create($request->except('price_per_sqm') + [
            'price_per_sqm' => $pricePerSqm,
            'is_corner' => $request->has('is_corner'),
            'is_furnished' => $request->has('is_furnished'),
        ]);

        return redirect()->route('admin.units.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Unit $unit)
    {
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();
        // Get models for the selected project
        $propertyModels = PropertyModel::where('project_id', $unit->project_id)->orderBy('name_en')->get();
        if ($propertyModels->isEmpty()) {
             $propertyModels = PropertyModel::orderBy('name_en')->get();
        }

        return view('admin.units.edit', compact('unit', 'projects', 'unitTypes', 'propertyModels'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'property_model_id' => 'nullable|exists:property_models,id',
            'unit_type_id' => 'required|exists:unit_types,id',
            'unit_number' => 'nullable|string|max:100',
            'price' => 'required|numeric',
            'currency_code' => 'required|string|size:3',
            'built_up_area' => 'nullable|numeric',
            'unit_status' => 'required|in:available,reserved,sold,rented',
        ]);

        $pricePerSqm = $unit->price_per_sqm;
        if ($request->filled('built_up_area') && $request->built_up_area > 0 && $request->price > 0) {
            $pricePerSqm = $request->price / $request->built_up_area;
        }

        $unit->update($request->except('price_per_sqm') + [
            'price_per_sqm' => $pricePerSqm,
            'is_corner' => $request->has('is_corner'),
            'is_furnished' => $request->has('is_furnished'),
        ]);

        return redirect()->route('admin.units.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('admin.units.index')
            ->with('success', __('admin.deleted_successfully'));
    }
}
