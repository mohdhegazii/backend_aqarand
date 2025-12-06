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
        $query = trim((string) $request->input('q'));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cities = City::with(['region', 'region.country'])
            ->where(function ($builder) use ($query) {
                $builder->where('name_en', 'like', "%{$query}%")
                    ->orWhere('name_local', 'like', "%{$query}%");
            })
            ->orderBy('name_en')
            ->limit(8)
            ->get()
            ->map(function (City $city) {
                return [
                    'type' => 'city',
                    'label' => trim($city->name_en . ' - ' . ($city->region->name_en ?? '')),
                    'path' => $city->region?->country?->name_en,
                    'country_id' => $city->region->country_id,
                    'region_id' => $city->region_id,
                    'city_id' => $city->id,
                    'district_id' => null,
                    'lat' => $city->lat,
                    'lng' => $city->lng,
                ];
            });

        $districts = District::with(['city', 'city.region', 'city.region.country'])
            ->where(function ($builder) use ($query) {
                $builder->where('name_en', 'like', "%{$query}%")
                    ->orWhere('name_local', 'like', "%{$query}%");
            })
            ->orderBy('name_en')
            ->limit(8)
            ->get()
            ->map(function (District $district) {
                $city = $district->city;
                $region = $city?->region;

                $cityName = $city?->name_en;
                $regionName = $region?->name_en;

                $labelParts = array_filter([
                    $district->name_en,
                    $cityName,
                ]);

                return [
                    'type' => 'district',
                    'label' => implode(' - ', $labelParts),
                    'path' => $region?->country?->name_en,
                    'country_id' => $region?->country_id,
                    'region_id' => $region?->id,
                    'city_id' => $city?->id,
                    'district_id' => $district->id,
                    'lat' => $district->lat ?? $city?->lat,
                    'lng' => $district->lng ?? $city?->lng,
                ];
            });

        return response()->json($cities->merge($districts)->values());
    }
}
