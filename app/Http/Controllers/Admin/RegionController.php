<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $query = Region::with('country');

        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%$search%")
                    ->orWhere('name_local', 'like', "%$search%");
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $regions = $query->paginate(10);
        $countries = Country::where('is_active', true)->get();

        return view('admin.regions.index', compact('regions', 'countries'));
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->get();
        return view('admin.regions.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
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

        if (Region::where('country_id', $validated['country_id'])->where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this country.']);
        }

        $region = Region::create(collect($validated)->except('boundary_geojson')->toArray());

        if ($request->filled('boundary_geojson')) {
            $region->update([
                'boundary' => DB::raw("ST_GeomFromGeoJSON(" . DB::connection()->getPdo()->quote($request->boundary_geojson) . ")")
            ]);
        }

        return redirect()->route($this->adminRoutePrefix().'regions.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function show(Region $region)
    {
        // Fetch boundary as GeoJSON for display
        $region = Region::select('*', DB::raw('ST_AsGeoJSON(boundary) as boundary_geojson'))->find($region->id);

        return view('admin.regions.show', compact('region'));
    }

    public function edit(Region $region)
    {
        $countries = Country::where('is_active', true)->get();

        // Fetch boundary as GeoJSON
        $region = Region::select('*', DB::raw('ST_AsGeoJSON(boundary) as boundary_geojson'))->find($region->id);

        return view('admin.regions.edit', compact('region', 'countries'));
    }

    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
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

        if ($slug !== $region->slug || $validated['country_id'] != $region->country_id) {
             if (Region::where('country_id', $validated['country_id'])->where('slug', $slug)->where('id', '!=', $region->id)->exists()) {
                 return back()->withInput()->withErrors(['name_en' => 'Slug generated from Name (EN) already exists in this country.']);
             }
        }

        $region->update(collect($validated)->except('boundary_geojson')->toArray());

        if ($request->filled('boundary_geojson')) {
            // We need to use a raw query or update with DB::raw
            // Since we just updated the model, we can do a second update or hook into it.
            // Using a separate update is cleaner for safety with raw queries.
            DB::table('regions')
                ->where('id', $region->id)
                ->update(['boundary' => DB::raw("ST_GeomFromGeoJSON(" . DB::connection()->getPdo()->quote($request->boundary_geojson) . ")")]);
        } elseif ($request->has('boundary_geojson') && empty($request->boundary_geojson)) {
             // Handle clearing of boundary if user deleted it
             DB::table('regions')
                ->where('id', $region->id)
                ->update(['boundary' => null]);
        }

        return redirect()->route($this->adminRoutePrefix().'regions.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Region $region)
    {
        $region->update(['is_active' => false]);

        return redirect()->route($this->adminRoutePrefix().'regions.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:regions,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Region::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
