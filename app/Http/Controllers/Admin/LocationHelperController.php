<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationHelperController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Search locations (Country, Region, City, District) for autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $isAr = app()->getLocale() === 'ar';

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Regions
        $regions = $this->locationService->searchRegions($query);

        foreach ($regions as $r) {
            $name = $isAr
                ? ($r->name_local ?? $r->name_en) . ' (' . ($r->country->name_local ?? $r->country->name_en) . ')'
                : $r->name_en . ' (' . $r->country->name_en . ')';

            $results[] = [
                'id' => $r->id,
                'name' => $name,
                'type' => 'region',
                'lat' => $r->lat,
                'lng' => $r->lng,
                'data' => [
                    'country_id' => $r->country_id,
                    'region_id' => $r->id,
                    'city_id' => null,
                    'district_id' => null
                ]
            ];
        }

        // Cities
        $cities = $this->locationService->searchCities($query);

        foreach ($cities as $c) {
            $name = $isAr
                ? ($c->name_local ?? $c->name_en) . ', ' . ($c->region->name_local ?? $c->region->name_en)
                : $c->name_en . ', ' . $c->region->name_en;

            $results[] = [
                'id' => $c->id,
                'name' => $name,
                'type' => 'city',
                'lat' => $c->lat,
                'lng' => $c->lng,
                'data' => [
                    'country_id' => $c->region->country_id,
                    'region_id' => $c->region_id,
                    'city_id' => $c->id,
                    'district_id' => null
                ]
            ];
        }

        // Districts
        $districts = $this->locationService->searchDistricts($query);

        foreach ($districts as $d) {
            $name = $isAr
                ? ($d->name_local ?? $d->name_en) . ', ' . ($d->city->name_local ?? $d->city->name_en)
                : $d->name_en . ', ' . $d->city->name_en;

            $results[] = [
                'id' => $d->id,
                'name' => $name,
                'type' => 'district',
                'lat' => $d->lat,
                'lng' => $d->lng,
                'data' => [
                    'country_id' => $d->city->region->country_id,
                    'region_id' => $d->city->region_id,
                    'city_id' => $d->city_id,
                    'district_id' => $d->id
                ]
            ];
        }

        return response()->json($results);
    }

    public function getRegions($countryId)
    {
        \Log::debug('[DEBUG][locations] getRegions called', [
            'country_id' => $countryId,
            'url' => request()->fullUrl(),
        ]);
        try {
            $regions = $this->locationService->getRegionsByCountry($countryId);
            \Log::debug('[DEBUG][locations] getRegions response count', [
                'count' => count($regions),
            ]);
            return response()->json(['regions' => $regions]);
        } catch (\Throwable $e) {
            \Log::error('[DEBUG][locations] getRegions exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities($regionId)
    {
        \Log::debug('[DEBUG][locations] getCities called', [
            'region_id' => $regionId,
            'url' => request()->fullUrl(),
        ]);
        try {
            $cities = $this->locationService->getCitiesByRegion($regionId);
            \Log::debug('[DEBUG][locations] getCities response count', [
                'count' => count($cities),
            ]);
            return response()->json(['cities' => $cities]);
        } catch (\Throwable $e) {
            \Log::error('[DEBUG][locations] getCities exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDistricts($cityId)
    {
        \Log::debug('[DEBUG][locations] getDistricts called', [
            'city_id' => $cityId,
            'url' => request()->fullUrl(),
        ]);
        try {
            $districts = $this->locationService->getDistrictsByCity($cityId);
            \Log::debug('[DEBUG][locations] getDistricts response count', [
                'count' => count($districts),
            ]);
            return response()->json(['districts' => $districts]);
        } catch (\Throwable $e) {
            \Log::error('[DEBUG][locations] getDistricts exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getProjects($districtId)
    {
        \Log::debug('[DEBUG][locations] getProjects called', ['district_id' => $districtId]);
        try {
            $projects = $this->locationService->getProjectsByDistrict($districtId);
            \Log::debug('[DEBUG][locations] getProjects response count', ['count' => count($projects)]);
            return response()->json(['projects' => $projects]);
        } catch (\Throwable $e) {
            \Log::error('[DEBUG][locations] getProjects exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
