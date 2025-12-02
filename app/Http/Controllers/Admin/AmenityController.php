<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        $query = Amenity::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%");
        }

        $amenities = $query->paginate(10);

        return view('admin.amenities.index', compact('amenities'));
    }

    public function create()
    {
        return view('admin.amenities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:120',
            'name_local' => 'required|string|max:120',
            'amenity_type' => 'required|in:project,unit,both',
            'icon_class' => 'nullable|string|max:120',
            'image_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        // Ensure slug uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while (Amenity::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        $validated['is_active'] = $request->has('is_active');

        Amenity::create($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', __('admin.save') . ' ' . __('admin.amenities'));
    }

    public function edit(Amenity $amenity)
    {
        return view('admin.amenities.edit', compact('amenity'));
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:120',
            'name_local' => 'required|string|max:120',
            'amenity_type' => 'required|in:project,unit,both',
            'icon_class' => 'nullable|string|max:120',
            'image_url' => 'nullable|url|max:255',
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

        $amenity->update($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', __('admin.save') . ' ' . __('admin.amenities'));
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return redirect()->route('admin.amenities.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.amenities'));
    }
}
