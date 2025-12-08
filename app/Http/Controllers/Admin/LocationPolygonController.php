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
        // Added viewport filtering: min_lat, max_lat, min_lng, max_lng

        $level = $request->query('level');
        $includeProjects = $request->boolean('include_projects');

        $minLat = $request->query('min_lat');
        $maxLat = $request->query('max_lat');
        $minLng = $request->query('min_lng');
        $maxLng = $request->query('max_lng');

        $hasBounds = $minLat !== null && $maxLat !== null && $minLng !== null && $maxLng !== null;

        // If no filter is provided, retain existing behavior (load all) for backward compatibility.
        // If bounds are provided, we should obey them, effectively "loading all within bounds" if level is null.
        $loadAll = is_null($level) && !$request->has('include_projects');

        // Helper to apply spatial filter if bounds exist (for Location entities)
        $applySpatialFilter = function($query) use ($hasBounds, $minLat, $maxLat, $minLng, $maxLng) {
            if ($hasBounds) {
                // Using MBRIntersects for bounding box intersection.
                // ST_MakeEnvelope(min_lng, min_lat, max_lng, max_lat)
                $query->whereRaw('MBRIntersects(boundary, ST_MakeEnvelope(?, ?, ?, ?))',
                    [$minLng, $minLat, $maxLng, $maxLat]);
            }
        };

        // Helper to apply point filter if bounds exist (for Projects)
        $applyPointFilter = function($query) use ($hasBounds, $minLat, $maxLat, $minLng, $maxLng) {
            if ($hasBounds) {
                $query->whereBetween('lat', [$minLat, $maxLat])
                      ->whereBetween('lng', [$minLng, $maxLng]);
            }
        };

        $data = [];

        if ($loadAll || $level === 'country') {
            $q = Country::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applySpatialFilter($q);

            $data['countries'] = $q->get()
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
            $q = Region::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applySpatialFilter($q);

            $data['regions'] = $q->get()
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
            $q = City::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applySpatialFilter($q);

            $data['cities'] = $q->get()
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
            $q = District::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applySpatialFilter($q);

            $data['districts'] = $q->get()
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
            $q = Project::query()
                ->select('id', 'name_en', 'name_ar', 'map_polygon', 'project_boundary_geojson')
                ->where(function($q) {
                    $q->whereNotNull('map_polygon')
                      ->orWhereNotNull('project_boundary_geojson');
                });

            $applyPointFilter($q);

            $data['projects'] = $q->get()
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
