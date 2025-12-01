<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $query = Region::with('country');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%");
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $regions = $query->paginate(10);
        $countries = Country::all(); // For filter

        return view('admin.regions.index', compact('regions', 'countries'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('admin.regions.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        // Check uniqueness
        if (Region::where('country_id', $validated['country_id'])->where('slug', $validated['slug'])->exists()) {
             // Append random string or fail
             // For now, fail with message
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this country.']);
        }

        Region::create($validated);

        return redirect()->route('admin.regions.index')
            ->with('success', __('admin.save') . ' ' . __('admin.region'));
    }

    public function edit(Region $region)
    {
        $countries = Country::all();
        return view('admin.regions.edit', compact('region', 'countries'));
    }

    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = $slug;

        // Check uniqueness if slug changed OR if country changed
        if ($slug !== $region->slug || $validated['country_id'] != $region->country_id) {
             if (Region::where('country_id', $validated['country_id'])->where('slug', $slug)->where('id', '!=', $region->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this country.']);
             }
        }

        $region->update($validated);

        return redirect()->route('admin.regions.index')
            ->with('success', __('admin.save') . ' ' . __('admin.region'));
    }

    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('admin.regions.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.region'));
    }
}
