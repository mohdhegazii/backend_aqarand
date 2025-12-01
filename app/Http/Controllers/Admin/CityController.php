<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with(['region.country']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%");
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $cities = $query->paginate(10);
        $regions = Region::with('country')->get(); // For filter

        return view('admin.cities.index', compact('cities', 'regions'));
    }

    public function create()
    {
        $regions = Region::with('country')->get();
        return view('admin.cities.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        // Check uniqueness
        if (City::where('region_id', $validated['region_id'])->where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this region.']);
        }

        City::create($validated);

        return redirect()->route('admin.cities.index')
            ->with('success', __('admin.save') . ' ' . __('admin.city'));
    }

    public function edit(City $city)
    {
        $regions = Region::with('country')->get();
        return view('admin.cities.edit', compact('city', 'regions'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $city->slug) {
             if (City::where('region_id', $validated['region_id'])->where('slug', $slug)->where('id', '!=', $city->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this region.']);
             }
             $validated['slug'] = $slug;
        }

        $city->update($validated);

        return redirect()->route('admin.cities.index')
            ->with('success', __('admin.save') . ' ' . __('admin.city'));
    }

    public function destroy(City $city)
    {
        $city->delete();

        return redirect()->route('admin.cities.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.city'));
    }
}
