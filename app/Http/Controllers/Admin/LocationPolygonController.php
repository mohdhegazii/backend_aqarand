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

        // Parse 'level' parameter to support comma-separated values (e.g. "country,region")
        $levelParam = $request->query('level');
        $levels = $levelParam ? explode(',', $levelParam) : [];

        // Support filtering by specific ID (e.g. "Get polygon for Region 5")
        $targetId = $request->query('id');

        $includeProjects = $request->boolean('include_projects');

        $minLat = $request->query('min_lat');
        $maxLat = $request->query('max_lat');
        $minLng = $request->query('min_lng');
        $maxLng = $request->query('max_lng');

        $hasBounds = $minLat !== null && $maxLat !== null && $minLng !== null && $maxLng !== null;

        // If no filter is provided, retain existing behavior (load all) for backward compatibility.
        // If bounds are provided, we should obey them, effectively "loading all within bounds" if level is null.
        // If ID is provided, we ignore bounds/viewport logic to ensure we get the specific entity.
        $loadAll = empty($levels) && !$request->has('include_projects') && !$targetId;

        // Helper to apply ID filter
        $applyIdFilter = function($query) use ($targetId) {
            if ($targetId) {
                $query->where('id', $targetId);
            }
        };

        // Helper to apply spatial filter if bounds exist (for Location entities)
        // This spatial query relies on locations_boundary_spatial_index (SPATIAL index on boundary column).
        // See docs/performance-guidelines.md for details on indexing and query optimization.
        $applySpatialFilter = function($query) use ($hasBounds, $minLat, $maxLat, $minLng, $maxLng, $targetId) {
            // ID filter takes precedence over spatial filter
            if ($targetId) return;

            if ($hasBounds) {
                // Using MBRIntersects for bounding box intersection against the spatial index.
                // ST_MakeEnvelope(min_lng, min_lat, max_lng, max_lat) creates a polygon from the viewport bounds.
                $query->whereRaw('MBRIntersects(boundary, ST_MakeEnvelope(?, ?, ?, ?))',
                    [$minLng, $minLat, $maxLng, $maxLat]);
            }
        };

        // Helper to apply point filter if bounds exist (for Projects)
        // This query is designed to use projects_lat_lng_index (composite B-Tree index on lat, lng) for viewport filtering.
        $applyPointFilter = function($query) use ($hasBounds, $minLat, $maxLat, $minLng, $maxLng, $targetId) {
            // ID filter takes precedence
            if ($targetId) return;

            if ($hasBounds) {
                // Ensure we use range conditions on the indexed columns directly.
                // EXPLAIN shows usage of index: projects_lat_lng_index
                $query->whereBetween('lat', [$minLat, $maxLat])
                      ->whereBetween('lng', [$minLng, $maxLng]);
            }
        };

        $data = [];

        if ($loadAll || in_array('country', $levels)) {
            $q = Country::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applyIdFilter($q);
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

        if ($loadAll || in_array('region', $levels)) {
            $q = Region::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applyIdFilter($q);
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

        if ($loadAll || in_array('city', $levels)) {
            $q = City::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applyIdFilter($q);
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

        if ($loadAll || in_array('district', $levels)) {
            $q = District::query()
                ->select('id', 'name_en', 'name_local', DB::raw('ST_AsGeoJSON(boundary) as polygon'))
                ->whereNotNull('boundary');

            $applyIdFilter($q);
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

        if ($loadAll || in_array('project', $levels) || $includeProjects) {
            // Optimize SELECT payload: return only fields needed by the frontend.
            // Including lat/lng for marker positioning, slug for links, min_price for potential filtering/display.
            $q = Project::query()
                ->select(
                    'id',
                    'name_en',
                    'name_ar',
                    'lat',
                    'lng',
                    'slug',
                    'min_price',
                    'map_polygon',
                    'project_boundary_geojson'
                )
                ->where(function($q) {
                    $q->whereNotNull('map_polygon')
                      ->orWhereNotNull('project_boundary_geojson');
                });

            $applyIdFilter($q);
            $applyPointFilter($q);

            $data['projects'] = $q->get()
                ->filter(function ($item) {
                    // Filter out invalid/empty boundaries if any
                    return !empty($item->boundary_geojson);
                })
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name_en ?? $item->name_ar,
                        'lat' => $item->lat,
                        'lng' => $item->lng,
                        'slug' => $item->slug,
                        'min_price' => $item->min_price,
                        'polygon' => $item->boundary_geojson, // Uses the accessor
                        'level' => 'project'
                    ];
                })
                ->values(); // Reset keys after filter
        }

        return response()->json($data);
    }
}
