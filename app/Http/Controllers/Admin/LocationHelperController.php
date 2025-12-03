<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationHelperController extends Controller
{
    public function getCountry($id)
    {
        $country = Country::with('regions')->find($id);
        return $country ?? response()->json(['error' => 'Not found'], 404);
    }

    public function getRegion($id)
    {
        $region = Region::with('cities')->find($id);
        return $region ?? response()->json(['error' => 'Not found'], 404);
    }

    public function getCity($id)
    {
        $city = City::with('districts')->find($id);
        return $city ?? response()->json(['error' => 'Not found'], 404);
    }

    public function searchLocations(Request $request)
    {
        $term = $request->query('q');
        if (!$term) {
            return response()->json([]);
        }

        $results = [];

        // Search Regions (Governorates)
        $regions = Region::where('name_en', 'like', "%{$term}%")
            ->orWhere('name_local', 'like', "%{$term}%")
            ->limit(5)
            ->get();

        foreach ($regions as $region) {
            $results[] = [
                'id' => $region->id,
                'type' => 'region',
                'name' => $region->name_en . ' / ' . $region->name_local . ' (Region)',
                'country_id' => $region->country_id,
            ];
        }

        // Search Cities
        $cities = City::where('name_en', 'like', "%{$term}%")
            ->orWhere('name_local', 'like', "%{$term}%")
            ->with('region')
            ->limit(5)
            ->get();

        foreach ($cities as $city) {
            $results[] = [
                'id' => $city->id,
                'type' => 'city',
                'name' => $city->name_en . ' / ' . $city->name_local . ' (City)',
                'country_id' => $city->region->country_id,
                'region_id' => $city->region_id,
            ];
        }

        // Search Districts
        $districts = District::where('name_en', 'like', "%{$term}%")
            ->orWhere('name_local', 'like', "%{$term}%")
            ->with('city.region')
            ->limit(5)
            ->get();

        foreach ($districts as $district) {
            $results[] = [
                'id' => $district->id,
                'type' => 'district',
                'name' => $district->name_en . ' / ' . $district->name_local . ' (District)',
                'country_id' => $district->city->region->country_id,
                'region_id' => $district->city->region_id,
                'city_id' => $district->city_id,
            ];
        }

        return response()->json($results);
    }
}
