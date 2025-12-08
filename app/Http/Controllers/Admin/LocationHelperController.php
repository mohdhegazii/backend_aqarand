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
        $regions = $this->locationService->getRegionsByCountry($countryId);
        return response()->json(['regions' => $regions]);
    }

    public function getCities($regionId)
    {
        $cities = $this->locationService->getCitiesByRegion($regionId);
        return response()->json(['cities' => $cities]);
    }

    public function getDistricts($cityId)
    {
        $districts = $this->locationService->getDistrictsByCity($cityId);
        return response()->json(['districts' => $districts]);
    }

    public function getProjects($districtId)
    {
        $projects = $this->locationService->getProjectsByDistrict($districtId);
        return response()->json(['projects' => $projects]);
    }
}
