<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\AmenityService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    protected $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    public function show($id)
    {
        $unit = Unit::findOrFail($id);

        $unitAmenities = $unit->amenities()
            ->where('is_active', true)
            ->with('category')
            ->get();

        $amenityDisplayGroups = $this->amenityService->formatAmenitiesForDisplay($unitAmenities);

        return view('frontend.units.show', compact('unit', 'amenityDisplayGroups'));
    }
}
