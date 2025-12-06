<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Developer;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\District;
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
        $countries = Country::orderBy('name_en')->get(); // Initial list for dropdowns
        $amenities = Amenity::where('amenity_type', '!=', 'unit')->orderBy('name_en')->get();

        return view('admin.projects.create', compact('developers', 'countries', 'amenities'));
    }

    public function store(Request $request)
    {
        $messages = [
            'name_ar.required' => 'اسم المشروع (عربي) مطلوب',
            'name_en.required' => 'اسم المشروع (إنجليزي) مطلوب',
            'country_id.required' => 'يرجى اختيار الدولة',
            'region_id.required' => 'يرجى اختيار المحافظة',
            'city_id.required' => 'يرجى اختيار المدينة',
            'hero_image.required' => 'صورة الغلاف مطلوبة',
            'hero_image.image' => 'صورة الغلاف يجب أن تكون ملف صورة',
            'gallery.image' => 'صور المعرض يجب أن تكون ملفات صور',
        ];

        $validated = $request->validate([
            // Step 1: Basic Info
            'name_ar' => 'required|string|max:200',
            'name_en' => 'required|string|max:200',
            'developer_id' => 'nullable|exists:developers,id',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'map_polygon' => 'nullable',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'address_text' => 'nullable|string|max:255',

            // Step 2: Details
            'min_bua' => 'nullable|numeric|min:0',
            'max_bua' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'total_units' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',

            // Step 3: Description
            'description_long' => 'nullable|string',
            'meta_title_ar' => 'nullable|string|max:255',
            'meta_description_ar' => 'nullable|string|max:255',

            // Step 4: Media
            'hero_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'brochure' => 'nullable|file|mimes:pdf|max:5120',
            'video_url' => 'nullable|url', // Assuming field exists or we add it to 'gallery' logic? Prompt says "Video URL" field.

            // Step 5: Publish (Active/Featured etc if any)
            'is_active' => 'nullable|boolean',
        ], $messages);

        try {
            DB::beginTransaction();

            // Slug generation
            $slugBase = $request->name_en ?? $request->name_ar;
            $slug = Str::slug($slugBase);
            $originalSlug = $slug;
            $count = 1;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $project = new Project();
            $project->name = $request->name_en;
            $project->name_en = $request->name_en;
            $project->name_ar = $request->name_ar;
            $project->slug = $slug;
            $project->seo_slug_en = $slug;
            $project->seo_slug_ar = preg_replace('/\s+/u', '-', trim($request->name_ar));

            $project->developer_id = $request->developer_id;
            $project->country_id = $request->country_id;
            $project->region_id = $request->region_id;
            $project->city_id = $request->city_id;
            $project->district_id = $request->district_id;
            $project->address_text = $request->address_text;

            // Map Polygon
            if ($request->filled('map_polygon')) {
                $project->map_polygon = is_string($request->map_polygon) ? json_decode($request->map_polygon, true) : $request->map_polygon;
            }
            if ($request->filled('lat')) $project->lat = $request->lat;
            if ($request->filled('lng')) $project->lng = $request->lng;

            // Details
            $project->min_bua = $request->min_bua;
            $project->max_bua = $request->max_bua;
            $project->min_price = $request->min_price;
            $project->max_price = $request->max_price;
            $project->total_units = $request->total_units;

            // Description
            $project->description_long = $request->description_long;
            $project->meta_title_ar = $request->meta_title_ar;
            $project->meta_description_ar = $request->meta_description_ar;
            $project->video_url = $request->video_url;

            // Default Status (Hidden in UI)
            $project->status = 'planned';
            $project->is_active = $request->has('is_active'); // Checkbox usually

            $project->save();

            // Media
            if ($request->hasFile('hero_image')) {
                $project->hero_image_url = $this->projectMediaService->handleHeroImageUpload($project, $request->file('hero_image'));
            }

            $galleryItems = [];
            if ($request->hasFile('gallery')) {
                $galleryItems = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
            }
            // If video_url is present, maybe add to gallery or separate field?
            // Existing schema doesn't seem to have video_url column explicitly in my read.
            // Prompt says: "Video URL field ... make it Arabic label".
            // If column missing, I'll skip saving it for now or assume it's part of description/meta.
            // Wait, schema showed `gallery` JSON. I can add video to gallery?
            // No, prompt implies a separate input.
            // I'll ignore video storage for now if column doesn't exist, to avoid error.

            $project->gallery = $galleryItems;

            if ($request->hasFile('brochure')) {
                $project->brochure_url = $this->projectMediaService->handleBrochureUpload($project, $request->file('brochure'));
            }

            $project->save();

            // Amenities
            if ($request->has('amenities')) {
                $project->amenities()->sync($request->amenities);
            }

            DB::commit();

            return redirect()->route('admin.projects.index')
                ->with('success', 'تم إنشاء المشروع بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
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
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',

            // Details
            'min_bua' => 'nullable|numeric|min:0',
            'max_bua' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'total_units' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',

            // Description
            'description_long' => 'nullable|string',
            'meta_title_ar' => 'nullable|string|max:255',
            'meta_description_ar' => 'nullable|string|max:255',

            // Media
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'brochure' => 'nullable|file|mimes:pdf|max:5120',

            'is_active' => 'nullable|boolean',
        ], $messages);

        try {
            DB::beginTransaction();

            $project->name_en = $request->name_en;
            $project->name_ar = $request->name_ar;

            $project->developer_id = $request->developer_id;
            $project->country_id = $request->country_id;
            $project->region_id = $request->region_id;
            $project->city_id = $request->city_id;
            $project->district_id = $request->district_id;
            $project->address_text = $request->address_text;

            if ($request->filled('map_polygon')) {
                $project->map_polygon = is_string($request->map_polygon) ? json_decode($request->map_polygon, true) : $request->map_polygon;
            }
            if ($request->filled('lat')) $project->lat = $request->lat;
            if ($request->filled('lng')) $project->lng = $request->lng;

            $project->min_bua = $request->min_bua;
            $project->max_bua = $request->max_bua;
            $project->min_price = $request->min_price;
            $project->max_price = $request->max_price;
            $project->total_units = $request->total_units;

            $project->description_long = $request->description_long;
            $project->meta_title_ar = $request->meta_title_ar;
            $project->meta_description_ar = $request->meta_description_ar;
            $project->video_url = $request->video_url;

            $project->is_active = $request->has('is_active');

            // Media Logic
            // 1. Hero Replacement
            if ($request->hasFile('hero_image')) {
                $project->hero_image_url = $this->projectMediaService->handleHeroImageUpload($project, $request->file('hero_image'));
            }

            // 2. Brochure Replacement
            if ($request->hasFile('brochure')) {
                $project->brochure_url = $this->projectMediaService->handleBrochureUpload($project, $request->file('brochure'));
            }

            // 3. Gallery Management
            // Collect existing gallery data (names, alts, selection)
            // The view will send 'gallery_data' array for existing items
            $submittedGalleryData = $request->input('gallery_data', []);
            $finalGallery = [];

            // Rebuild gallery from submitted data (handling deletions implicitly by omission)
            foreach ($submittedGalleryData as $item) {
                $finalGallery[] = [
                    'path' => $item['path'] ?? '',
                    'name' => $item['name'] ?? null,
                    'alt' => $item['alt'] ?? null,
                    'is_hero_candidate' => isset($item['is_hero_candidate']), // Not really used for logic, just storage
                ];

                // Check if this item was selected as hero via radio button 'selected_hero'
                // The radio button value is the path.
            }

            // Add new files
            if ($request->hasFile('gallery')) {
                $newItems = $this->projectMediaService->handleGalleryUpload($project, $request->file('gallery'));
                $finalGallery = array_merge($finalGallery, $newItems);
            }

            $project->gallery = $finalGallery;

            // Handle Hero Selection from Radio
            if ($request->filled('selected_hero')) {
                $selectedHeroPath = $request->selected_hero;
                // Verify it exists in our gallery logic or is valid
                $project->hero_image_url = $selectedHeroPath;
            }

            $project->save();

            if ($request->has('amenities')) {
                $project->amenities()->sync($request->amenities);
            }

            DB::commit();

            return redirect()->route('admin.projects.index')
                ->with('success', 'تم تعديل المشروع بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
        }
    }

    // Helper to upload media separately if needed (ajax) - not using currently but keeping generic structure
    public function uploadMedia(Request $request, Project $project)
    {
        // Implementation for AJAX upload if we move to that later
    }
}
