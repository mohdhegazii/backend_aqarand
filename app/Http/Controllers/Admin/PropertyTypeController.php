<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PropertyTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyType::query();

        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

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
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $originalSlug = $slug;
        $counter = 1;
        while (PropertyType::where('slug', $slug)->exists()) {
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

        PropertyType::create($validated);

        return redirect()->route('admin.property-types.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(PropertyType $propertyType)
    {
        try {
            return view('admin.property_types.edit', compact('propertyType'))->render();
        } catch (\Throwable $e) {
            dd('DEBUG CAUGHT ERROR IN PropertyTypeController::edit', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        }
    }

    public function update(Request $request, PropertyType $propertyType)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'category' => 'required|in:' . implode(',', PropertyType::CATEGORIES),
            'icon_class' => 'nullable|string|max:120',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

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

        if ($request->hasFile('image')) {
            if ($propertyType->image_path) {
                Storage::disk('public')->delete($propertyType->image_path);
            }
            $path = $request->file('image')->store('lookups', 'public');
            $validated['image_path'] = $path;
            $validated['image_url'] = Storage::url($path);
        }

        $propertyType->update($validated);

        return redirect()->route('admin.property-types.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(PropertyType $propertyType)
    {
        $propertyType->update(['is_active' => false]);

        return redirect()->route('admin.property-types.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:property_types,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        PropertyType::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
