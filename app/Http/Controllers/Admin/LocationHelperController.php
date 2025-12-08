<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\District;
use App\Models\Project;

class LocationHelperController extends Controller
{
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
        $regions = Region::where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->limit(5)
            ->with('country')
            ->get();

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
        $cities = City::where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->limit(5)
            ->with(['region', 'region.country'])
            ->get();

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
        $districts = District::where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->limit(5)
            ->with(['city', 'city.region', 'city.region.country'])
            ->get();

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
        $regions = Region::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get();

        return response()->json(['regions' => $regions]);
    }

    public function getCities($regionId)
    {
        $cities = City::where('region_id', $regionId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get();

        return response()->json(['cities' => $cities]);
    }

    public function getDistricts($cityId)
    {
        $districts = District::where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get();

        return response()->json(['districts' => $districts]);
    }

    public function getProjects($districtId)
    {
        $projects = Project::where('district_id', $districtId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->orderBy('name_ar')
            ->get(['id', 'name_en', 'name_ar', 'name', 'district_id', 'map_lat', 'map_lng', 'lat', 'lng', 'map_zoom', 'map_polygon']);

        return response()->json(['projects' => $projects]);
    }
}
