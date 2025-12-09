<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AmenityAnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected $amenityAnalyticsService;

    public function __construct(AmenityAnalyticsService $amenityAnalyticsService)
    {
        $this->amenityAnalyticsService = $amenityAnalyticsService;
    }

    public function index(): View
    {
        $topProjectAmenities = $this->amenityAnalyticsService->getTopProjectAmenities(5);

        return view('admin.dashboard', compact('topProjectAmenities'));
    }
}
