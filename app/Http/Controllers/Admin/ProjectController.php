<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Developer;
use App\Models\Country;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
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

        $project = Project::create($request->except('amenities', 'seo_slug_en', 'seo_slug_ar') + [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'seo_slug_en' => $validated['seo_slug_en'],
            'seo_slug_ar' => $validated['seo_slug_ar'],
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('amenities')) {
            $project->amenities()->sync($request->amenities);
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
        ]);

        // Auto-generate slugs if missing
        if (empty($validated['seo_slug_en'])) {
            $validated['seo_slug_en'] = Str::slug($validated['name_en']);
        }

        if (empty($validated['seo_slug_ar']) && $request->filled('name_ar')) {
             $validated['seo_slug_ar'] = preg_replace('/\s+/u', '-', trim($request->name_ar));
        }

        $data = $request->except('amenities', 'seo_slug_en', 'seo_slug_ar');
        $data['seo_slug_en'] = $validated['seo_slug_en'];
        $data['seo_slug_ar'] = $validated['seo_slug_ar'] ?? null;
        $data['is_active'] = $request->has('is_active');

        $project->update($data);

        if ($request->has('amenities')) {
            $project->amenities()->sync($request->amenities);
        }

        return redirect()->route('admin.projects.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')
            ->with('success', __('admin.deleted_successfully'));
    }
}
