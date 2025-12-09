<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\District;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    /**
     * Get active regions for a country.
     *
     * @param int|Country $country
     * @return Collection
     */
    public function getRegionsByCountry($country): Collection
    {
        $countryId = $country instanceof Country ? $country->id : $country;

        return Region::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'country_id', 'name_en', 'name_local', 'slug', 'lat', 'lng']);
    }

    /**
     * Get active cities for a region.
     *
     * @param int|Region $region
     * @return Collection
     */
    public function getCitiesByRegion($region): Collection
    {
        $regionId = $region instanceof Region ? $region->id : $region;

        return City::where('region_id', $regionId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'region_id', 'name_en', 'name_local', 'slug', 'lat', 'lng']);
    }

    /**
     * Get active districts for a city.
     *
     * @param int|City $city
     * @return Collection
     */
    public function getDistrictsByCity($city): Collection
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return District::where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'city_id', 'name_en', 'name_local', 'slug', 'lat', 'lng']);
    }

    /**
     * Get active projects for a district.
     *
     * @param int|District $district
     * @return Collection
     */
    public function getProjectsByDistrict($district): Collection
    {
        $districtId = $district instanceof District ? $district->id : $district;

        return Project::where('district_id', $districtId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->orderBy('name_ar')
            ->get(['id', 'name_en', 'name_ar', 'name', 'district_id', 'map_lat', 'map_lng', 'lat', 'lng', 'map_zoom', 'map_polygon']);
    }

    /**
     * Search for regions by name.
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function searchRegions(string $query, int $limit = 5): Collection
    {
        return Region::where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->limit($limit)
            ->with('country')
            ->get();
    }

    /**
     * Search for cities by name.
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function searchCities(string $query, int $limit = 5): Collection
    {
        return City::where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->limit($limit)
            ->with(['region', 'region.country'])
            ->get();
    }

    /**
     * Search for districts by name.
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function searchDistricts(string $query, int $limit = 5): Collection
    {
        return District::where('name_en', 'like', "%{$query}%")
            ->orWhere('name_local', 'like', "%{$query}%")
            ->limit($limit)
            ->with(['city', 'city.region', 'city.region.country'])
            ->get();
    }
}
