<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Developer;
use App\Models\Country;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ProjectMediaService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $developers = Developer::orderBy('name')->get();

        return view('admin.projects.index', compact('projects', 'developers'));
    }

    public function create()
    {
        $developers = Developer::orderBy('name')->get();
        // Countries are needed for cascading dropdowns (initially hidden but data needed)
        $countries = Country::orderBy('name_en')->get();
        $amenities = Amenity::where('amenity_type', '!=', 'unit')->orderBy('name_en')->get();

        return view('admin.projects.create', compact('developers', 'countries', 'amenities'));
    }

    public function store(Request $request)
    {
        // Custom messages for Arabic requirements
        $messages = [
            'name_ar.required' => 'اسم المشروع (عربي) مطلوب',
            'country_id.required' => 'يرجى اختيار الدولة',
            'region_id.required' => 'يرجى اختيار المحافظة',
            'city_id.required' => 'يرجى اختيار المدينة',
            'hero_image.required' => 'صورة الغلاف مطلوبة',
            'hero_image.image' => 'صورة الغلاف يجب أن تكون ملف صورة',
            'gallery.image' => 'صور المعرض يجب أن تكون ملفات صور',
        ];

        $validated = $request->validate([
            'name_en' => 'nullable|string|max:200', // Made nullable as we prioritize Arabic for now? Prompt says "All labels... Arabic". But DB might require name_en. Let's keep strict if DB requires.
            // DB schema: name VARCHAR(200) NOT NULL. name_en/ar are bilingual.
            // Usually 'name' is filled from name_en or name_ar.
            // I will require name_ar as per Arabic UI focus, and name_en optional or derived.
            // But let's check schema again. `name` is NOT NULL. `name_en` is NOT NULL (in first schema).
            // Memory says "Multilingual support... uses name_en and name_local/name_ar".
            // I'll require name_ar and name_en.
            'name_ar' => 'required|string|max:200',
            'name_en' => 'required|string|max:200',

            'developer_id' => 'nullable|exists:developers,id',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',

            // Status/Delivery Year hidden from UI, so not validated.

            'map_polygon' => 'nullable', // JSON string or array

            // Media
            'hero_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'brochure' => 'nullable|file|mimes:pdf|max:5120',
        ], $messages);

        try {
            // Slug generation
            $slugBase = $request->name_en ?? $request->name_ar;
            $slug = Str::slug($slugBase);

            // Ensure uniqueness
            $originalSlug = $slug;
            $count = 1;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $project = new Project();
            $project->name = $request->name_en; // Fallback or strict
            $project->name_en = $request->name_en;
            $project->name_ar = $request->name_ar;
            $project->slug = $slug;
            $project->seo_slug_en = $slug;
            $project->seo_slug_ar = preg_replace('/\s+/u', '-', trim($request->name_ar)); // Arabic slug

            $project->developer_id = $request->developer_id;
            $project->country_id = $request->country_id;
            $project->region_id = $request->region_id;
            $project->city_id = $request->city_id;
            $project->district_id = $request->district_id;
            $project->address_text = $request->address_text; // Assuming field exists

            // Map Polygon
            if ($request->filled('map_polygon')) {
                // Decode if string, or assign if array
                $polygon = is_string($request->map_polygon) ? json_decode($request->map_polygon, true) : $request->map_polygon;
                $project->map_polygon = $polygon;

                // Also update lat/lng from center if possible, or use request lat/lng
            }
            if ($request->filled('lat')) $project->lat = $request->lat;
            if ($request->filled('lng')) $project->lng = $request->lng;

            $project->description_long = $request->description_long; // or description_ar/en

            // Default Status
            $project->status = 'planned';
            $project->is_active = true;

            $project->save();

            // Media Handling
            // 1. Hero Image (Required on create)
            if ($request->hasFile('hero_image')) {
                $heroPath = $this->projectMediaService->handleHeroImageUpload($project, $request->file('hero_image'));
                $project->hero_image_url = $heroPath;
            }

            // 2. Gallery
            $galleryItems = [];
            if ($request->hasFile('gallery')) {
                $galleryItems = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
            }
            $project->gallery = $galleryItems;

            // 3. Brochure
            if ($request->hasFile('brochure')) {
                $brochurePath = $this->projectMediaService->handleBrochureUpload($project, $request->file('brochure'));
                $project->brochure_url = $brochurePath;
            }

            $project->save();

            // Amenities
            if ($request->has('amenities')) {
                $project->amenities()->sync($request->amenities);
            }

            return redirect()->route('admin.projects.index')
                ->with('success', 'تم إنشاء المشروع بنجاح');

        } catch (\Throwable $e) {
            dd($e); // As per instructions for debugging
        }
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
        $messages = [
            'name_ar.required' => 'اسم المشروع (عربي) مطلوب',
            'name_en.required' => 'اسم المشروع (إنجليزي) مطلوب',
        ];

        $validated = $request->validate([
            'name_ar' => 'required|string|max:200',
            'name_en' => 'required|string|max:200',

            'developer_id' => 'nullable|exists:developers,id',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',

            'map_polygon' => 'nullable',

            // Media
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Optional on edit
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'brochure' => 'nullable|file|mimes:pdf|max:5120',
        ], $messages);

        try {
            $project->name_en = $request->name_en;
            $project->name_ar = $request->name_ar;
            // Should we update slug? Usually explicit only. Skip for now to avoid breaking URLs.

            $project->developer_id = $request->developer_id;
            $project->country_id = $request->country_id;
            $project->region_id = $request->region_id;
            $project->city_id = $request->city_id;
            $project->district_id = $request->district_id;

            if ($request->filled('map_polygon')) {
                $polygon = is_string($request->map_polygon) ? json_decode($request->map_polygon, true) : $request->map_polygon;
                $project->map_polygon = $polygon;
            }
            if ($request->filled('lat')) $project->lat = $request->lat;
            if ($request->filled('lng')) $project->lng = $request->lng;

            $project->description_long = $request->description_long;

            // Media Handling

            // 1. Hero Image Upload (Direct replacement)
            if ($request->hasFile('hero_image')) {
                $project->hero_image_url = $this->projectMediaService->handleHeroImageUpload($project, $request->file('hero_image'));
            }

            // 2. Gallery Management
            // Existing gallery data from form (includes names, alts, and selection)
            $submittedGalleryData = $request->input('gallery_data', []);
            // e.g. [ 0 => {path:..., name:..., alt:..., is_hero_candidate:...}, ... ]

            // Rebuild gallery array
            // We only keep items present in $submittedGalleryData
            // If an item is missing from $submittedGalleryData but was in DB, it is considered deleted.
            // Note: Users might delete via JS which removes the input.

            $finalGallery = [];
            foreach ($submittedGalleryData as $item) {
                // Ensure structure
                $finalGallery[] = [
                    'path' => $item['path'] ?? '',
                    'name' => $item['name'] ?? null,
                    'alt' => $item['alt'] ?? null,
                    'is_hero_candidate' => isset($item['is_hero_candidate']), // radio or checkbox
                ];
            }

            // 3. New Gallery Uploads
            if ($request->hasFile('gallery')) {
                $newItems = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
                $finalGallery = array_merge($finalGallery, $newItems);
            }

            $project->gallery = $finalGallery;

            // 4. Hero Selection from Gallery
            // If "selected_hero" input is present (radio button value = path)
            if ($request->filled('selected_hero')) {
                $project->hero_image_url = $request->selected_hero;
            }

            // 5. Brochure
            if ($request->hasFile('brochure')) {
                $project->brochure_url = $this->projectMediaService->handleBrochureUpload($project, $request->file('brochure'));
            }

            $project->save();

            // Amenities
            if ($request->has('amenities')) {
                $project->amenities()->sync($request->amenities);
            }

            return redirect()->route('admin.projects.index')
                ->with('success', 'تم تعديل المشروع بنجاح');

        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
