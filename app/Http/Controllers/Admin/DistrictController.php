<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $query = District::with(['city.region']);

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

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $districts = $query->paginate(10);
        $cities = City::where('is_active', true)->with('region')->get();

        return view('admin.districts.index', compact('districts', 'cities'));
    }

    public function create()
    {
        $cities = City::where('is_active', true)->with('region')->get();
        return view('admin.districts.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'boundary_geojson' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if (District::where('city_id', $validated['city_id'])->where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this city.']);
        }

        $district = District::create(collect($validated)->except('boundary_geojson')->toArray());

        if ($request->filled('boundary_geojson')) {
            $district->update([
                'boundary' => DB::raw("ST_GeomFromGeoJSON(" . DB::connection()->getPdo()->quote($request->boundary_geojson) . ")")
            ]);
        }

        return redirect()->route($this->adminRoutePrefix().'districts.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function show(District $district)
    {
        $district = District::select('*', DB::raw('ST_AsGeoJSON(boundary) as boundary_geojson'))->find($district->id);
        return view('admin.districts.show', compact('district'));
    }

    public function edit(District $district)
    {
        $cities = City::where('is_active', true)->with('region')->get();
        $district = District::select('*', DB::raw('ST_AsGeoJSON(boundary) as boundary_geojson'))->find($district->id);
        return view('admin.districts.edit', compact('district', 'cities'));
    }

    public function update(Request $request, District $district)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'boundary_geojson' => 'nullable|json',
        ]);

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = $slug;

        if ($slug !== $district->slug || $validated['city_id'] != $district->city_id) {
             if (District::where('city_id', $validated['city_id'])->where('slug', $slug)->where('id', '!=', $district->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this city.']);
             }
        }

        $district->update(collect($validated)->except('boundary_geojson')->toArray());

        if ($request->filled('boundary_geojson')) {
            DB::table('districts')
                ->where('id', $district->id)
                ->update(['boundary' => DB::raw("ST_GeomFromGeoJSON(" . DB::connection()->getPdo()->quote($request->boundary_geojson) . ")")]);
        } elseif ($request->has('boundary_geojson') && empty($request->boundary_geojson)) {
             DB::table('districts')
                ->where('id', $district->id)
                ->update(['boundary' => null]);
        }

        return redirect()->route($this->adminRoutePrefix().'districts.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(District $district)
    {
        $district->update(['is_active' => false]);

        return redirect()->route($this->adminRoutePrefix().'districts.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:districts,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        District::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
