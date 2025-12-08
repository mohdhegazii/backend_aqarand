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
    public function index()
    {
        // For performance, we limit polygons to avoid crashing the browser if there are thousands.
        // However, for now, we assume reasonable limits.

        $countries = Country::query()
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

        $regions = Region::query()
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

        $cities = City::query()
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

        $districts = District::query()
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

        $projects = Project::query()
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

        return response()->json([
            'countries' => $countries,
            'regions' => $regions,
            'cities' => $cities,
            'districts' => $districts,
            'projects' => $projects,
        ]);
    }
}
