<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\District;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationPolygonController extends Controller
{
    public function index(Request $request)
    {
        // For performance, we limit polygons to avoid crashing the browser if there are thousands.
        // We introduce filters 'level' and 'include_projects' to reduce payload size.

        $level = $request->query('level');
        $includeProjects = $request->boolean('include_projects');

        // If no filter is provided, retain existing behavior (load all) for backward compatibility.
        $loadAll = is_null($level) && !$request->has('include_projects');

        $data = [];

        if ($loadAll || $level === 'country') {
            $data['countries'] = Country::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->getDisplayNameAttribute(),
                        'polygon' => json_decode($item->polygon),
                        'level' => 'country'
                    ];
                });
        }

        if ($loadAll || $level === 'region') {
            $data['regions'] = Region::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->getDisplayNameAttribute(),
                        'polygon' => json_decode($item->polygon),
                        'level' => 'region'
                    ];
                });
        }

        if ($loadAll || $level === 'city') {
            $data['cities'] = City::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->getDisplayNameAttribute(),
                        'polygon' => json_decode($item->polygon),
                        'level' => 'city'
                    ];
                });
        }

        if ($loadAll || $level === 'district') {
            $data['districts'] = District::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->getDisplayNameAttribute(),
                        'polygon' => json_decode($item->polygon),
                        'level' => 'district'
                    ];
                });
        }

        if ($loadAll || $level === 'project' || $includeProjects) {
            $data['projects'] = Project::query()
                ->select('id', 'name_en', 'name_ar', 'map_polygon', 'project_boundary_geojson')
                ->where(function($q) {
                    $q->whereNotNull('map_polygon')
                      ->orWhereNotNull('project_boundary_geojson');
                })
                ->get()
                ->filter(function ($item) {
                    return !empty($item->boundary_geojson);
                })
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name_en ?? $item->name_ar,
                        'polygon' => $item->boundary_geojson, // Uses the accessor
                        'level' => 'project'
                    ];
                })
                ->values(); // Reset keys after filter
        }

        return response()->json($data);
    }
}
