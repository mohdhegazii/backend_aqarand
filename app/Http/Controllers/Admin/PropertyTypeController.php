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
            'category' => 'required|in:residential,commercial,administrative,medical,mixed,other',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if (PropertyType::where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists.']);
        }

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
            'category' => 'required|in:residential,commercial,administrative,medical,mixed,other',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $propertyType->slug) {
             if (PropertyType::where('slug', $slug)->where('id', '!=', $propertyType->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists.']);
             }
             $validated['slug'] = $slug;
        }

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
