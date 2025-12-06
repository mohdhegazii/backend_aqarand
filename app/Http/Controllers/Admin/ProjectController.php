<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Developer;
use App\Models\Country;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Media\MediaProcessor;
use App\Services\ProjectMediaService;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    protected $projectMediaService;

    public function __construct(ProjectMediaService $projectMediaService)
    {
        $this->projectMediaService = $projectMediaService;
    }

    public function index(Request $request)
    {
        $query = Project::with(['developer', 'city', 'district']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('developer_id')) {
            $query->where('developer_id', $request->developer_id);
        }

        $projects = $query->latest()->paginate(10);
        $developers = Developer::orderBy('name')->get(); // For filter

        return view('admin.projects.index', compact('projects', 'developers'));
    }

    public function create()
    {
        $developers = Developer::orderBy('name')->get();
        $countries = Country::orderBy('name_en')->get();
        $amenities = Amenity::where('amenity_type', '!=', 'unit')->orderBy('name_en')->get();

        return view('admin.projects.create', compact('developers', 'countries', 'amenities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:200',
            'name_ar' => 'nullable|string|max:200',
            'developer_id' => 'nullable|exists:developers,id',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'status' => 'required',
            'delivery_year' => 'nullable|integer|min:2000|max:2100',
            'seo_slug_en' => 'nullable|string|unique:projects,seo_slug_en',
            'seo_slug_ar' => 'nullable|string|unique:projects,seo_slug_ar',
            'amenities' => 'array',
            'amenities.*' => 'exists:amenities,id',
            'main_keyword_en' => 'nullable|string|max:255',
            'main_keyword_ar' => 'nullable|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_title_ar' => 'nullable|string|max:255',
            'meta_description_en' => 'nullable|string|max:255',
            'meta_description_ar' => 'nullable|string|max:255',
            'tagline_en' => 'nullable|string|max:255',
            'tagline_ar' => 'nullable|string|max:255',

            // Media Validation
            // Hero image is selected from gallery, so not required as separate upload
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'brochure' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Auto-generate slugs if missing
        $validated['name'] = $validated['name_en']; // Fallback

        // Logic: seo_slug_en defaults to slug(name_en), seo_slug_ar defaults to slug(name_ar)
        $validated['seo_slug_en'] = !empty($validated['seo_slug_en']) ? $validated['seo_slug_en'] : Str::slug($validated['name_en']);

        // Arabic Slug - user specifically asked: "seo slug Arabic is the Arabic Name"
        $nameAr = $request->input('name_ar');
        if (empty($validated['seo_slug_ar']) && !empty($nameAr)) {
             $validated['seo_slug_ar'] = preg_replace('/\s+/u', '-', trim($nameAr));
        }

        // Main slug fallback
        $validated['slug'] = $validated['seo_slug_en'];

        $project = Project::create($request->except('amenities', 'seo_slug_en', 'seo_slug_ar', 'hero_image', 'gallery', 'brochure') + [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'seo_slug_en' => $validated['seo_slug_en'],
            'seo_slug_ar' => $validated['seo_slug_ar'],
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('amenities')) {
            $project->amenities()->sync($request->amenities);
        }

        // Media Handling
        $updates = [];
        $galleryPaths = [];

        if ($request->hasFile('gallery')) {
            $galleryPaths = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
            $updates['gallery'] = $galleryPaths;

            // Set first gallery image as hero by default on create
            if (!empty($galleryPaths) && isset($galleryPaths[0])) {
                $updates['hero_image_url'] = $galleryPaths[0];
            }
        }

        if ($request->hasFile('brochure')) {
            $updates['brochure_url'] = $this->projectMediaService->handleBrochureUpload($project, $request->file('brochure'));
        }

        if (!empty($updates)) {
            $project->update($updates);
        }

        return redirect()->route('admin.projects.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Project $project)
    {
        $developers = Developer::orderBy('name')->get();
        $countries = Country::orderBy('name_en')->get();
        $amenities = Amenity::where('amenity_type', '!=', 'unit')->orderBy('name_en')->get();

        return view('admin.projects.edit', compact('project', 'developers', 'countries', 'amenities'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:200',
            'name_ar' => 'nullable|string|max:200',
            'developer_id' => 'nullable|exists:developers,id',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'status' => 'required',
            'delivery_year' => 'nullable|integer|min:2000|max:2100',
            'seo_slug_en' => 'nullable|string|unique:projects,seo_slug_en,' . $project->id,
            'seo_slug_ar' => 'nullable|string|unique:projects,seo_slug_ar,' . $project->id,
            'amenities' => 'array',
            'amenities.*' => 'exists:amenities,id',
            'main_keyword_en' => 'nullable|string|max:255',
            'main_keyword_ar' => 'nullable|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_title_ar' => 'nullable|string|max:255',
            'meta_description_en' => 'nullable|string|max:255',
            'meta_description_ar' => 'nullable|string|max:255',
            'tagline_en' => 'nullable|string|max:255',
            'tagline_ar' => 'nullable|string|max:255',

            // Media Validation (Optional on update)
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'brochure' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Auto-generate slugs if missing
        if (empty($validated['seo_slug_en'])) {
            $validated['seo_slug_en'] = Str::slug($validated['name_en']);
        }

        if (empty($validated['seo_slug_ar']) && $request->filled('name_ar')) {
             $validated['seo_slug_ar'] = preg_replace('/\s+/u', '-', trim($request->name_ar));
        }

        $data = $request->except('amenities', 'seo_slug_en', 'seo_slug_ar', 'hero_image', 'gallery', 'brochure');
        $data['seo_slug_en'] = $validated['seo_slug_en'];
        $data['seo_slug_ar'] = $validated['seo_slug_ar'] ?? null;
        $data['is_active'] = $request->has('is_active');

        // Handle secondary keywords as JSON array if passed as CSV or array
        if ($request->has('secondary_keywords_en_str')) {
             $data['secondary_keywords_en'] = array_map('trim', explode(',', $request->secondary_keywords_en_str));
        }
        if ($request->has('secondary_keywords_ar_str')) {
             $data['secondary_keywords_ar'] = array_map('trim', explode(',', $request->secondary_keywords_ar_str));
        }

        $project->update($data);

        if ($request->has('amenities')) {
            $project->amenities()->sync($request->amenities);
        }

        // Media Handling
        // Update Hero Image from selection
        if ($request->filled('selected_hero')) {
            $project->hero_image_url = $request->selected_hero;
        }

        // Gallery: Append new images
        if ($request->hasFile('gallery')) {
            $newGallery = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
            $currentGallery = $project->gallery ?? [];
            $project->gallery = array_merge($currentGallery, $newGallery);
        }

        // Brochure: Delete old if new is uploaded
        if ($request->hasFile('brochure')) {
            if ($project->brochure_url && Storage::disk('public')->exists($project->brochure_url)) {
                Storage::disk('public')->delete($project->brochure_url);
            }
            $project->brochure_url = $this->projectMediaService->handleBrochureUpload($project, $request->file('brochure'));
        }

        $project->save();

        return redirect()->route('admin.projects.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function uploadMedia(Request $request, Project $project, MediaProcessor $mediaProcessor)
    {
        $request->validate([
            'file' => 'required|image|max:10240', // 10MB
        ]);

        try {
            $mediaProcessor->storeProjectImage($project, $request->file('file'));
            return response()->json(['success' => true, 'message' => __('admin.created_successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
