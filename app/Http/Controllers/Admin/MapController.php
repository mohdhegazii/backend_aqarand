<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Project;
use App\Models\Region;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function boundaries()
    {
        // Fetch all active entities that have a boundary_polygon
        // We select minimal fields needed for the map

        $countries = Country::whereNotNull('boundary_polygon')
            ->where('is_active', true)
            ->get(['id', 'name_en', 'name_local', 'boundary_polygon', 'code']);

        $regions = Region::whereNotNull('boundary_polygon')
            ->where('is_active', true)
            ->get(['id', 'name_en', 'name_local', 'boundary_polygon', 'slug']);

        $cities = City::whereNotNull('boundary_polygon')
            ->where('is_active', true)
            ->get(['id', 'name_en', 'name_local', 'boundary_polygon', 'slug']);

        $districts = District::whereNotNull('boundary_polygon')
            ->where('is_active', true)
            ->get(['id', 'name_en', 'name_local', 'boundary_polygon', 'slug']);

        // For projects, we use boundary_polygon.
        $projects = Project::whereNotNull('boundary_polygon')
            ->where('is_active', true)
            ->get(['id', 'name_en', 'name_ar', 'boundary_polygon', 'slug']);

        return view('admin.map.boundaries', compact('countries', 'regions', 'cities', 'districts', 'projects'));
    }
}
