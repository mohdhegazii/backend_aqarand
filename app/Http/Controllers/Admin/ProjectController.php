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
            'main_keyword_en' => 'nullable|string',
            'main_keyword_ar' => 'nullable|string',

            // Media Validation
            'hero_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
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

        if ($request->hasFile('hero_image')) {
            $updates['hero_image_url'] = $this->projectMediaService->handleHeroImageUpload($project, $request->file('hero_image'));
        }

        if ($request->hasFile('gallery')) {
            $updates['gallery'] = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
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
            'main_keyword_en' => 'nullable|string',
            'main_keyword_ar' => 'nullable|string',

            // Media Validation (Optional on update)
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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
        // Hero Image: Delete old if new is uploaded
        if ($request->hasFile('hero_image')) {
            if ($project->hero_image_url && Storage::disk('public')->exists($project->hero_image_url)) {
                Storage::disk('public')->delete($project->hero_image_url);
            }
            $project->hero_image_url = $this->projectMediaService->handleHeroImageUpload($project, $request->file('hero_image'));
        }

        // Gallery: Replace completely or append?
        // Instructions say: "Decide behavior: Append to existing gallery, OR Replace completely (choose one and document in comments)."
        // Common admin behavior is Append if the field allows multi-select upload, but often "replacing" gallery needs a way to delete old ones.
        // Since we don't have a UI to delete individual images yet, "Replace Completely" is dangerous (user might just want to add one).
        // "Append" is safer. The user can't delete yet without a delete UI, but that's better than losing all images.
        // I will choose APPEND.
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
