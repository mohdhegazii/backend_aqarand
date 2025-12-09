<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Project;
use App\Models\PropertyModel;
use App\Models\UnitType;
use App\Http\Requests\Admin\Units\StoreUnitRequest;
use App\Http\Requests\Admin\Units\UpdateUnitRequest;
use App\Services\AmenityService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    protected $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    public function index(Request $request)
    {
        $query = Unit::with(['project', 'propertyModel', 'unitType']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $units = $query->latest()->paginate(10);
        // Use async search for filter instead of loading all projects if possible,
        // but for now keeping index as is or updating it too?
        // Instructions said: "Eliminate heavy usages... in admin create/edit forms."
        // Index filters might also benefit, but let's prioritize forms first.
        // However, loading ALL projects for index filter is also bad.
        // I will empty the projects list here and let the view handle it if I updated the index view (which I haven't yet).
        // The plan didn't explicitly mention index views but "Identify all admin forms".
        // Let's stick to create/edit for now to be safe, but removing unused vars.
        $projects = []; // Replaced by async search in view if implemented, or just empty to avoid load.
        // Actually, if I empty it, the index filter might break if it expects $projects.
        // Let's leave index alone unless I fix the view.
        // But for create/edit:

        $projects = Project::orderBy('name_en')->limit(10)->get(); // Limit for filter initial load

        // If filtering by specific project, ensure it's in the list for display
        if ($request->filled('project_id')) {
            $filteredProject = Project::find($request->project_id);
            if ($filteredProject && !$projects->contains('id', $filteredProject->id)) {
                $projects->push($filteredProject);
            }
        }

        return view('admin.units.index', compact('units', 'projects'));
    }

    public function create()
    {
        // Removed heavy loads: $projects, $propertyModels

        // Pass empty unitTypes/propertyTypes arrays as required by the property-hierarchy-picker component logic
        // even if it loads them via AJAX. This prevents "Undefined variable" errors in the view.
        $unitTypes = [];
        $propertyTypes = [];
        $categories = []; // Component uses categories too

        // Need to provide initial categories list though?
        $categories = \App\Models\Category::orderBy('name_en')->get(); // Categories are usually few.

        $amenitiesByCategory = $this->amenityService->getAmenitiesGroupedByCategory('unit', true);

        return view('admin.units.create', compact('categories', 'unitTypes', 'propertyTypes', 'amenitiesByCategory'));
    }

    public function store(StoreUnitRequest $request)
    {
        $validated = $request->validated();

        // Auto calculate price per sqm
        $pricePerSqm = null;
        if ($request->built_up_area > 0 && $request->price > 0) {
            $pricePerSqm = $request->price / $request->built_up_area;
        }

        $unit = Unit::create($request->except(['price_per_sqm', 'amenities']) + [
            'price_per_sqm' => $pricePerSqm,
            'construction_status' => $request->construction_status ?? 'new_launch',
            'is_corner' => $request->has('is_corner'),
            'is_furnished' => $request->has('is_furnished'),
        ]);

        if ($request->has('amenities')) {
            $unit->amenities()->sync($request->input('amenities', []));
        }

        return redirect()->route($this->adminRoutePrefix().'units.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Unit $unit)
    {
        // Removed heavy loads.
        // Data is now loaded via AJAX or existing unit relationships.

        // Populate lists for the hierarchy picker based on current selection
        $categories = \App\Models\Category::orderBy('name_en')->get();

        // We need to resolve propertyTypes and unitTypes based on the unit's hierarchy
        // Unit -> PropertyModel -> UnitType -> PropertyType -> Category
        // Or Unit -> UnitType -> PropertyType -> Category (if direct relationship exists)
        // Unit has unit_type_id directly? No, unit links to property_model usually?
        // Let's check Unit model relationships or schema.
        // UnitController index uses: Unit::with(['project', 'propertyModel', 'unitType']);
        // So unit has unit_type_id directly.

        $unitTypes = [];
        $propertyTypes = [];

        if ($unit->unitType) {
            $propertyType = $unit->unitType->propertyType;
             if ($propertyType) {
                 $propertyTypes = \App\Models\PropertyType::where('category_id', $propertyType->category_id)->get();
                 $unitTypes = \App\Models\UnitType::where('property_type_id', $propertyType->id)->get();
             }
        }

        $amenitiesByCategory = $this->amenityService->getAmenitiesGroupedByCategory('unit', true);
        $selectedAmenityIds = $unit->amenities()->pluck('amenities.id')->toArray();

        return view('admin.units.edit', compact('unit', 'categories', 'unitTypes', 'propertyTypes', 'amenitiesByCategory', 'selectedAmenityIds'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $validated = $request->validated();

        $pricePerSqm = $unit->price_per_sqm;
        if ($request->filled('built_up_area') && $request->built_up_area > 0 && $request->price > 0) {
            $pricePerSqm = $request->price / $request->built_up_area;
        }

        $unit->update($request->except(['price_per_sqm', 'amenities']) + [
            'price_per_sqm' => $pricePerSqm,
            'is_corner' => $request->has('is_corner'),
            'is_furnished' => $request->has('is_furnished'),
        ]);

        // Sync amenities. Note: If 'amenities' checkbox array is not present (all unchecked),
        // standard browser behavior does not send the key.
        // $request->input('amenities', []) defaults to [] if not present.
        // So we can just sync that default.
        $unit->amenities()->sync($request->input('amenities', []));

        return redirect()->route($this->adminRoutePrefix().'units.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route($this->adminRoutePrefix().'units.index')
            ->with('success', __('admin.deleted_successfully'));
    }
}
