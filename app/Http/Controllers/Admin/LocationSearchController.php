<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;

class LocationSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        // Search Cities
        $cities = City::with(['region', 'region.country'])
            ->where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'type' => 'city',
                    'name' => $city->name_en . ' / ' . $city->name_local,
                    'text' => $city->name_en, // For display
                    'country_id' => $city->region->country_id,
                    'region_id' => $city->region_id,
                    'city_id' => $city->id,
                    'district_id' => null, // Needs selection
                ];
            });

        // Search Districts
        $districts = District::with(['city', 'city.region', 'city.region.country'])
            ->where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->take(10)
            ->get()
            ->map(function ($district) {
                return [
                    'id' => $district->id,
                    'type' => 'district',
                    'name' => $district->name_en . ' / ' . $district->name_local . ' (' . $district->city->name_en . ')',
                    'text' => $district->name_en,
                    'country_id' => $district->city->region->country_id,
                    'region_id' => $district->city->region_id,
                    'city_id' => $district->city_id,
                    'district_id' => $district->id,
                ];
            });

        return response()->json($cities->merge($districts));
    }
}
