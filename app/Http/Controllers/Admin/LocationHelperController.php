<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\Request;

class LocationHelperController extends Controller
{
    public function getCountry($id)
    {
        return Country::find($id) ?? response()->json(['error' => 'Not found'], 404);
    }

    public function getRegion($id)
    {
        return Region::find($id) ?? response()->json(['error' => 'Not found'], 404);
    }

    public function getCity($id)
    {
        return City::find($id) ?? response()->json(['error' => 'Not found'], 404);
    }
}
