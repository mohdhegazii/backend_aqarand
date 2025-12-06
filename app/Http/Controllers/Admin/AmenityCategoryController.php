<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmenityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmenityCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = AmenityCategory::query();

        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_ar', 'like', "%$search%");
        }

        $query->orderBy('sort_order')->orderBy('name_en');

        $categories = $query->paginate(10);

        return view('admin.amenity_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.amenity_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:150'],
            'name_ar' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = Str::slug($validated['name_en']);
        $originalSlug = $slug;
        $counter = 1;
        while (AmenityCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $request->input('sort_order', 0);

        AmenityCategory::create($validated);

        return redirect()->route($this->adminRoutePrefix().'amenity-categories.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(AmenityCategory $amenityCategory)
    {
        return view('admin.amenity_categories.edit', compact('amenityCategory'));
    }

    public function update(Request $request, AmenityCategory $amenityCategory)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:150'],
            'name_ar' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = Str::slug($validated['name_en']);
        if ($slug !== $amenityCategory->slug) {
             $originalSlug = $slug;
             $counter = 1;
             while (AmenityCategory::where('slug', $slug)->where('id', '!=', $amenityCategory->id)->exists()) {
                 $slug = $originalSlug . '-' . $counter;
                 $counter++;
             }
             $validated['slug'] = $slug;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $request->input('sort_order', 0);

        $amenityCategory->update($validated);

        return redirect()->route($this->adminRoutePrefix().'amenity-categories.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(AmenityCategory $amenityCategory)
    {
        // Check if has amenities
        if ($amenityCategory->amenities()->count() > 0) {
             // Maybe prevent delete? Or just nullify.
             // FK is set null on delete so we can just delete.
        }

        $amenityCategory->delete();

        return redirect()->route($this->adminRoutePrefix().'amenity-categories.index')
            ->with('success', __('admin.deleted_successfully'));
    }
}
