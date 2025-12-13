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
            'featured_media_id' => 'nullable|integer|exists:media_files,id',
        ]);

        if ($id) {
            $project = Project::findOrFail($id);
        } else {
            $project = new Project();
        }

        $project->name_ar = $validated['name_ar'];
        $project->name_en = $validated['name_en'];

        // Ensure legacy 'name' column is populated.
        // We use getAttributes() to check the raw value because getNameAttribute() accessor
        // might return name_en/name_ar, masking the fact that 'name' column is actually null.
        if (!isset($project->getAttributes()['name']) || empty($project->getAttributes()['name'])) {
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

        // Handle Featured Media
        if ($request->has('featured_media_id')) {
            $mediaId = $request->input('featured_media_id');
            // Check if HasMedia trait is used
            if (in_array(\App\Models\Traits\HasMedia::class, class_uses($project))) {
                if ($mediaId) {
                    $project->syncMedia($mediaId, 'featured');
                } else {
                    $project->detachMedia('featured');
                }
            }
        }

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

        // Redirect to Step 4 (Media) directly, skipping Marketing (Step 3) for now.
        return redirect()->route('admin.projects.steps.media', ['project' => $project->id])
                         ->with('success', __('admin.saved_successfully'));
    }

    /**
     * Show Step 4: Media
     */
    public function showMediaStep($id)
    {
        $project = Project::findOrFail($id);

        // Fetch attached media
        // Gallery
        $galleryMedia = $project->mediaLinks()
            ->where('role', 'gallery')
            ->orderBy('ordering')
            ->with('mediaFile')
            ->get()
            ->pluck('mediaFile');

        // Brochure
        $brochureMedia = $project->mediaLinks()
            ->where('role', 'brochure')
            ->with('mediaFile')
            ->first();
        $brochureMediaFile = $brochureMedia ? $brochureMedia->mediaFile : null;

        // Featured
        $featuredMedia = $project->featuredMedia();
        $featuredMediaId = $featuredMedia ? $featuredMedia->id : null;

        $initialGalleryMedia = $galleryMedia->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => $m->url,
                'original_name' => $m->original_name ?? $m->original_filename,
                'alt_text' => $m->alt_text,
                'caption' => $m->caption,
                'variants' => $m->variants,
            ];
        })->values();

        return view('admin.projects.steps.media', compact('project', 'galleryMedia', 'brochureMediaFile', 'featuredMediaId', 'initialGalleryMedia'));
    }

    /**
     * Store/Update Step 4: Media
     */
    public function storeMediaStep(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // ---------------------------------------------------------------------
        // 1. Decode JSON inputs
        // ---------------------------------------------------------------------
        // 'gallery_media_ids' might be a JSON string (from new media-picker) or array.
        if ($request->has('gallery_media_ids') && is_string($request->input('gallery_media_ids'))) {
            $decoded = json_decode($request->input('gallery_media_ids'), true);
            if (is_array($decoded)) {
                $request->merge(['gallery_media_ids' => $decoded]);
            }
        }

        // 'gallery_alt_map' is a JSON string: { "id": "alt_text", ... }
        if ($request->has('gallery_alt_map') && is_string($request->input('gallery_alt_map'))) {
            $decodedAlts = json_decode($request->input('gallery_alt_map'), true);
            if (is_array($decodedAlts)) {
                $request->merge(['gallery_alt_map' => $decodedAlts]);
            }
        }

        // ---------------------------------------------------------------------
        // 2. Validation
        // ---------------------------------------------------------------------
        $request->validate([
            'gallery_media_ids' => 'nullable|array',
            'gallery_media_ids.*' => 'integer|exists:media_files,id',
            'gallery_alt_map' => 'nullable|array', // Keys are IDs, values are strings
            'featured_media_id' => 'nullable|integer|exists:media_files,id',
            'brochure_media_id' => 'nullable|integer|exists:media_files,id',
        ]);

        // ---------------------------------------------------------------------
        // 3. Update Alt Texts
        // ---------------------------------------------------------------------
        if ($request->has('gallery_alt_map')) {
            $altMap = $request->input('gallery_alt_map', []);
            foreach ($altMap as $mediaId => $altText) {
                if ($mediaId && is_string($altText)) {
                    \App\Models\MediaFile::where('id', $mediaId)->update(['alt_text' => trim($altText)]);
                }
            }
        }

        // ---------------------------------------------------------------------
        // 4. Sync Gallery
        // ---------------------------------------------------------------------
        // The input 'gallery_media_ids' contains IDs in the desired order.
        if ($request->has('gallery_media_ids')) {
             $project->syncMedia($request->input('gallery_media_ids', []), 'gallery');
        } else {
             // If not present (empty), clear gallery?
             // Be careful: if field is missing from request, it might mean "no change" or "empty".
             // With JSON input hidden field, an empty list sends "[]", which is parsed to empty array.
             // If the input was completely missing, $request->has() would be false.
             // Given the form implementation, it sends "[]" if empty.
             if ($request->filled('gallery_media_ids') === false && $request->has('gallery_media_ids')) {
                  $project->detachMedia('gallery');
             }
        }

        // ---------------------------------------------------------------------
        // 5. Sync Featured
        // ---------------------------------------------------------------------
        if ($request->has('featured_media_id')) {
            $featuredId = $request->input('featured_media_id');
            if ($featuredId) {
                $project->syncMedia($featuredId, 'featured');
            } else {
                $project->detachMedia('featured');
            }
        }

        // ---------------------------------------------------------------------
        // 6. Sync Brochure
        // ---------------------------------------------------------------------
        if ($request->has('brochure_media_id')) {
            $brochureId = $request->input('brochure_media_id');
            if ($brochureId) {
                $project->syncMedia($brochureId, 'brochure');
            } else {
                $project->detachMedia('brochure');
            }
        }

        // Redirect to Index or Next Step (Financials - Step 5).
        return redirect()->route('admin.projects.index')
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
