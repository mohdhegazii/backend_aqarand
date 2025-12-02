<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        $query = Amenity::query()->with('category');

        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('name_en', 'like', "%$search%")
                    ->orWhere('name_local', 'like', "%$search%");
            });
        }

        $amenities = $query->paginate(10);

        return view('admin.amenities.index', compact('amenities'));
    }

    public function create()
    {
        // Using AmenityCategory instead of Category
        $categories = AmenityCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        return view('admin.amenities.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amenity_category_id' => 'nullable|exists:amenity_categories,id',
            'name_en' => 'required|string|max:120',
            'name_local' => 'required|string|max:120',
            'amenity_type' => 'required|in:project,unit,both',
            'icon_class' => 'nullable|string|max:120',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $originalSlug = $slug;
        $counter = 1;
        while (Amenity::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lookups', 'public');
            $validated['image_path'] = $path;
            $validated['image_url'] = Storage::url($path);
        }

        Amenity::create($validated);

        return redirect()->route('admin.amenities.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Amenity $amenity)
    {
        $categories = AmenityCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        return view('admin.amenities.edit', compact('amenity', 'categories'));
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'amenity_category_id' => 'nullable|exists:amenity_categories,id',
            'name_en' => 'required|string|max:120',
            'name_local' => 'required|string|max:120',
            'amenity_type' => 'required|in:project,unit,both',
            'icon_class' => 'nullable|string|max:120',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        if ($slug !== $amenity->slug) {
             $originalSlug = $slug;
             $counter = 1;
             while (Amenity::where('slug', $slug)->where('id', '!=', $amenity->id)->exists()) {
                 $slug = $originalSlug . '-' . $counter;
                 $counter++;
             }
             $validated['slug'] = $slug;
        }

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($amenity->image_path) {
                Storage::disk('public')->delete($amenity->image_path);
            }
            $path = $request->file('image')->store('lookups', 'public');
            $validated['image_path'] = $path;
            $validated['image_url'] = Storage::url($path);
        }

        $amenity->update($validated);

        return redirect()->route('admin.amenities.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->update(['is_active' => false]);

        return redirect()->route('admin.amenities.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:amenities,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Amenity::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
