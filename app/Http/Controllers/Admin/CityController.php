<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with(['region.country']);

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

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $cities = $query->paginate(10);
        $regions = Region::where('is_active', true)->with('country')->get();

        return view('admin.cities.index', compact('cities', 'regions'));
    }

    public function create()
    {
        $regions = Region::where('is_active', true)->with('country')->get();
        return view('admin.cities.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'boundary_geojson' => 'nullable|json',
            'boundary_polygon' => 'nullable|json',
        ]);

        if ($request->filled('boundary_polygon')) {
            $validated['boundary_polygon'] = json_decode($request->boundary_polygon, true);
        }

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        if (City::where('region_id', $validated['region_id'])->where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this region.']);
        }

        $city = City::create(collect($validated)->except('boundary_geojson')->toArray());

        if ($request->filled('boundary_geojson')) {
            $city->update([
                'boundary' => DB::raw("ST_GeomFromGeoJSON(" . DB::connection()->getPdo()->quote($request->boundary_geojson) . ")")
            ]);
        }

        return redirect()->route($this->adminRoutePrefix().'cities.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function show(City $city)
    {
        $city = City::select('*', DB::raw('ST_AsGeoJSON(boundary) as boundary_geojson'))->find($city->id);
        return view('admin.cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        $regions = Region::where('is_active', true)->with('country')->get();
        $city = City::select('*', DB::raw('ST_AsGeoJSON(boundary) as boundary_geojson'))->find($city->id);
        return view('admin.cities.edit', compact('city', 'regions'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'boundary_geojson' => 'nullable|json',
            'boundary_polygon' => 'nullable|json',
        ]);

        if ($request->filled('boundary_polygon')) {
            $validated['boundary_polygon'] = json_decode($request->boundary_polygon, true);
        }

        $slug = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = $slug;

        if ($slug !== $city->slug || $validated['region_id'] != $city->region_id) {
             if (City::where('region_id', $validated['region_id'])->where('slug', $slug)->where('id', '!=', $city->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this region.']);
             }
        }

        $city->update(collect($validated)->except('boundary_geojson')->toArray());

        if ($request->filled('boundary_geojson')) {
            DB::table('cities')
                ->where('id', $city->id)
                ->update(['boundary' => DB::raw("ST_GeomFromGeoJSON(" . DB::connection()->getPdo()->quote($request->boundary_geojson) . ")")]);
        } elseif ($request->has('boundary_geojson') && empty($request->boundary_geojson)) {
             DB::table('cities')
                ->where('id', $city->id)
                ->update(['boundary' => null]);
        }

        return redirect()->route($this->adminRoutePrefix().'cities.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(City $city)
    {
        $city->update(['is_active' => false]);

        return redirect()->route($this->adminRoutePrefix().'cities.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:cities,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        City::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
