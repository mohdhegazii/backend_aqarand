<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%");
        }

        $propertyTypes = $query->paginate(10);

        return view('admin.property_types.index', compact('propertyTypes'));
    }

    public function create()
    {
        return view('admin.property_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'category' => 'required|in:' . implode(',', PropertyType::CATEGORIES),
            'icon_class' => 'nullable|string|max:120',
            'image_url' => 'nullable|string|max:255', // Changed from url validation to string to allow relative paths or simple filenames if needed, though URL is better if full URL
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        // Ensure slug uniqueness by appending a suffix if needed
        $originalSlug = $slug;
        $counter = 1;
        while (PropertyType::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        $validated['is_active'] = $request->has('is_active');

        PropertyType::create($validated);

        return redirect()->route('admin.property-types.index')
            ->with('success', __('admin.save') . ' ' . __('admin.property_types'));
    }

    public function edit(PropertyType $propertyType)
    {
        return view('admin.property_types.edit', compact('propertyType'));
    }

    public function update(Request $request, PropertyType $propertyType)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'category' => 'required|in:' . implode(',', PropertyType::CATEGORIES),
            'icon_class' => 'nullable|string|max:120',
            'image_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Regenerate slug if name changed, but ensure uniqueness
        $slug = Str::slug($validated['name_en']);
        if ($slug !== $propertyType->slug) {
             $originalSlug = $slug;
             $counter = 1;
             while (PropertyType::where('slug', $slug)->where('id', '!=', $propertyType->id)->exists()) {
                 $slug = $originalSlug . '-' . $counter;
                 $counter++;
             }
             $validated['slug'] = $slug;
        }

        $validated['is_active'] = $request->has('is_active');

        $propertyType->update($validated);

        return redirect()->route('admin.property-types.index')
            ->with('success', __('admin.save') . ' ' . __('admin.property_types'));
    }

    public function destroy(PropertyType $propertyType)
    {
        $propertyType->delete();

        return redirect()->route('admin.property-types.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.property_types'));
    }
}
