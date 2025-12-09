<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Project;
use App\Models\Country;
use App\Services\DeveloperService;
use App\Services\AmenityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectWizardController extends Controller
{
    protected $developerService;
    protected $amenityService;

    public function __construct(DeveloperService $developerService, AmenityService $amenityService)
    {
        $this->developerService = $developerService;
        $this->amenityService = $amenityService;
    }

    /**
     * Show Step 1: Basics and Location
     * Note: Currently only Basics (name_ar, name_en) are implemented as per requirements.
     */
    public function showBasicsStep($id = null)
    {
        $project = $id ? Project::findOrFail($id) : new Project();
        $developers = $this->developerService->getActiveDevelopers();

        // Fetch all active countries
        $countries = Country::where('is_active', true)->get();

        // Set default country (Egypt) if new project
        $defaultCountryCode = 'EG';
        $defaultCountry = $countries->first(function ($country) use ($defaultCountryCode) {
            return $country->code === $defaultCountryCode || $country->name_en === 'Egypt';
        });

        if (!$project->exists && $defaultCountry && !$project->country_id) {
             $project->country_id = $defaultCountry->id;
        }

        // Load hierarchy based on project's country (or default)
        $regions = $project->country_id ? \App\Models\Region::where('country_id', $project->country_id)->get() : collect();
        $cities = $project->region_id ? \App\Models\City::where('region_id', $project->region_id)->get() : collect();
        $districts = $project->city_id ? \App\Models\District::where('city_id', $project->city_id)->get() : collect();

        return view('admin.projects.steps.basics', compact('project', 'developers', 'countries', 'regions', 'cities', 'districts'));
    }

    /**
     * Store/Update Step 1: Basics
     */
    public function storeBasicsStep(Request $request, $id = null)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'developer_id' => 'required|integer|exists:developers,id',
            'launch_date' => 'nullable|date', // Project Launch Date
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'boundary_geojson' => 'nullable|json',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($id) {
            $project = Project::findOrFail($id);
        } else {
            $project = new Project();
        }

        $project->name_ar = $validated['name_ar'];
        $project->name_en = $validated['name_en'];
        // Provide default for legacy 'name' column if missing
        if (empty($project->name)) {
            $project->name = $validated['name_en'];
        }
        $project->developer_id = $validated['developer_id'];
        $project->launch_date = $validated['launch_date'] ?? null;

        $project->country_id = $validated['country_id'];
        $project->region_id = $validated['region_id'];
        $project->city_id = $validated['city_id'];
        $project->district_id = $validated['district_id'] ?? null;

        // Save coordinates
        $project->lat = $validated['lat'] ?? null;
        $project->lng = $validated['lng'] ?? null;

        if (!empty($validated['boundary_geojson'])) {
             $project->boundary_geojson = json_decode($validated['boundary_geojson'], true);
        } else {
             $project->boundary_geojson = null;
        }

        // Auto-generate slug from English name if not already set or if creating new
        if (!$project->exists) {
            $project->slug = $this->generateUniqueSlug($validated['name_en']);

            // Default to active so it appears in lists immediately
            $project->is_active = true;
        }

        $project->save();

        return redirect()->route('admin.projects.steps.amenities', ['project' => $project->id])
                         ->with('success', __('admin.saved_successfully'));
    }

    /**
     * Show Step 2: Amenities
     */
    public function showAmenitiesStep($id)
    {
        $project = Project::findOrFail($id);

        // Fetch grouped amenities using service
        $amenitiesByCategory = $this->amenityService->getAmenitiesGroupedByCategory('project', true);

        // Fetch currently selected amenity IDs
        $selectedAmenityIds = $project->amenities()->pluck('amenities.id')->toArray();

        return view('admin.projects.steps.amenities', compact('project', 'amenitiesByCategory', 'selectedAmenityIds'));
    }

    /**
     * Store/Update Step 2: Amenities
     */
    public function storeAmenitiesStep(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        // Sync amenities
        $project->amenities()->sync($request->input('amenities', []));

        // For now, redirect back or to index if it's the last step.
        // Assuming there will be more steps later, but for now let's redirect back with success.
        // Or if this is the last implemented step, maybe to index?
        // Let's redirect to the same step for now to allow review, or maybe back to index if done.
        // The user requirement said: "Make sure the new Amenities tab/step integrates smoothly... next/previous buttons".
        // The view has "Save" button.

        return redirect()->route('admin.projects.steps.amenities', ['project' => $project->id])
                         ->with('success', __('admin.saved_successfully'));
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
