<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyModel;
use App\Models\Project;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyModelController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyModel::with(['project', 'unitType']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('unit_type_id')) {
            $query->where('unit_type_id', $request->unit_type_id);
        }

        $propertyModels = $query->latest()->paginate(10);
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();

        return view('admin.property_models.index', compact('propertyModels', 'projects', 'unitTypes'));
    }

    public function create()
    {
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();
        return view('admin.property_models.create', compact('projects', 'unitTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
        ]);

        // Auto-generate slugs
        $validated['name'] = $validated['name_en']; // Fallback
        if (empty($validated['seo_slug_en'])) {
            $validated['seo_slug_en'] = Str::slug($validated['name_en']);
        }

        PropertyModel::create($request->except('seo_slug_en', 'seo_slug_ar') + [
            'name' => $validated['name'],
            'seo_slug' => $validated['seo_slug_en'], // legacy/fallback
            'seo_slug_en' => $validated['seo_slug_en'],
            'seo_slug_ar' => $validated['seo_slug_ar'] ?? ($request->has('name_ar') ? Str::slug($request->name_ar) : null),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.property-models.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(PropertyModel $propertyModel)
    {
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();
        return view('admin.property_models.edit', compact('propertyModel', 'projects', 'unitTypes'));
    }

    public function update(Request $request, PropertyModel $propertyModel)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'unit_type_id' => 'required|exists:unit_types,id',
            'name_en' => 'required|string|max:200',
            'name_ar' => 'nullable|string|max:200',
            'code' => 'nullable|string|max:50',
            'seo_slug_en' => 'nullable|string|unique:property_models,seo_slug_en,' . $propertyModel->id,
        ]);

        if (empty($validated['seo_slug_en'])) {
            $validated['seo_slug_en'] = Str::slug($validated['name_en']);
        }

        $data = $request->except('seo_slug_en', 'seo_slug_ar');
        $data['seo_slug_en'] = $validated['seo_slug_en'];
        $data['seo_slug_ar'] = $request->seo_slug_ar ?? ($request->has('name_ar') ? Str::slug($request->name_ar) : null);
        $data['is_active'] = $request->has('is_active');

        $propertyModel->update($data);

        return redirect()->route('admin.property-models.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(PropertyModel $propertyModel)
    {
        $propertyModel->delete();
        return redirect()->route('admin.property-models.index')
            ->with('success', __('admin.deleted_successfully'));
    }
}
