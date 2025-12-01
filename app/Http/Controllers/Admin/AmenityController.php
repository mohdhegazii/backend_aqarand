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
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if (Amenity::where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists.']);
        }

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
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $amenity->slug) {
             if (Amenity::where('slug', $slug)->where('id', '!=', $amenity->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists.']);
             }
             $validated['slug'] = $slug;
        }

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
