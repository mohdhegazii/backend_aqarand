<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyModel;
use App\Models\Project;
use App\Models\UnitType;
use App\Http\Requests\Admin\PropertyModels\StorePropertyModelRequest;
use App\Http\Requests\Admin\PropertyModels\UpdatePropertyModelRequest;
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

    public function create(Request $request)
    {
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();
        $projectId = $request->input('project_id');
        $redirectTo = $request->input('redirect');

        return view('admin.property_models.create', compact('projects', 'unitTypes', 'projectId', 'redirectTo'));
    }

    public function store(StorePropertyModelRequest $request)
    {
        $validated = $request->validated();

        // Auto-generate slugs
        $validated['name'] = $validated['name_en']; // Fallback
        if (empty($validated['seo_slug_en'])) {
            $validated['seo_slug_en'] = Str::slug($validated['name_en']);
        }

        $propertyModel = PropertyModel::create($request->except('seo_slug_en', 'seo_slug_ar') + [
            'name' => $validated['name'],
            'seo_slug' => $validated['seo_slug_en'], // legacy/fallback
            'seo_slug_en' => $validated['seo_slug_en'],
            'seo_slug_ar' => $validated['seo_slug_ar'] ?? ($request->has('name_ar') ? Str::slug($request->name_ar) : null),
            'is_active' => $request->has('is_active'),
        ]);

        $redirectUrl = $this->resolveRedirect($request, $propertyModel->project_id);

        return redirect($redirectUrl)
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(PropertyModel $propertyModel, Request $request)
    {
        $projects = Project::orderBy('name_en')->get();
        $unitTypes = UnitType::orderBy('name_en')->get();
        $redirectTo = $request->input('redirect');
        $lockedProjectId = $request->input('project_id');

        return view('admin.property_models.edit', compact('propertyModel', 'projects', 'unitTypes', 'redirectTo', 'lockedProjectId'));
    }

    public function update(UpdatePropertyModelRequest $request, PropertyModel $propertyModel)
    {
        $validated = $request->validated();

        if (empty($validated['seo_slug_en'])) {
            $validated['seo_slug_en'] = Str::slug($validated['name_en']);
        }

        $data = $request->except('seo_slug_en', 'seo_slug_ar');
        $data['seo_slug_en'] = $validated['seo_slug_en'];
        $data['seo_slug_ar'] = $request->seo_slug_ar ?? ($request->has('name_ar') ? Str::slug($request->name_ar) : null);
        $data['is_active'] = $request->has('is_active');

        $propertyModel->update($data);

        $redirectUrl = $this->resolveRedirect($request, $propertyModel->project_id);

        return redirect($redirectUrl)
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(PropertyModel $propertyModel)
    {
        $projectId = $propertyModel->project_id;
        $propertyModel->delete();

        $redirectUrl = $this->resolveRedirect(request(), $projectId);

        return redirect($redirectUrl)
            ->with('success', __('admin.deleted_successfully'));
    }

    private function resolveRedirect(Request $request, int $projectId): string
    {
        $fallback = route($this->adminRoutePrefix().'property-models.index');
        $redirectUrl = $request->input('redirect');

        if ($redirectUrl && Str::startsWith($redirectUrl, url('/'))) {
            return $redirectUrl;
        }

        return route($this->adminRoutePrefix().'projects.edit', $projectId) ?? $fallback;
    }
}
