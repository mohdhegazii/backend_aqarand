<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $query = District::with(['city.region']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%");
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $districts = $query->paginate(10);
        $cities = City::with('region')->get(); // For filter

        return view('admin.districts.index', compact('districts', 'cities'));
    }

    public function create()
    {
        $cities = City::with('region')->get();
        return view('admin.districts.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        // Check uniqueness
        if (District::where('city_id', $validated['city_id'])->where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this city.']);
        }

        District::create($validated);

        return redirect()->route('admin.districts.index')
            ->with('success', __('admin.save') . ' ' . __('admin.district')); // Assuming translation exists
    }

    public function edit(District $district)
    {
        $cities = City::with('region')->get();
        return view('admin.districts.edit', compact('district', 'cities'));
    }

    public function update(Request $request, District $district)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $district->slug) {
             if (District::where('city_id', $validated['city_id'])->where('slug', $slug)->where('id', '!=', $district->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this city.']);
             }
             $validated['slug'] = $slug;
        }

        $district->update($validated);

        return redirect()->route('admin.districts.index')
            ->with('success', __('admin.save') . ' ' . __('admin.district'));
    }

    public function destroy(District $district)
    {
        $district->delete();

        return redirect()->route('admin.districts.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.district'));
    }
}
