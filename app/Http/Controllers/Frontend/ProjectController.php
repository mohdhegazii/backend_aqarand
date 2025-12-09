<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AmenityService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    public function show($slug)
    {
        $project = Project::where('slug', $slug)
            ->orWhere('id', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $projectAmenities = $project->amenities()
            ->where('is_active', true)
            ->with('category')
            ->get();

        $amenityDisplayGroups = $this->amenityService->formatAmenitiesForDisplay($projectAmenities);
        $topAmenities = $this->amenityService->getTopAmenitiesForProject($project, 3);

        return view('frontend.projects.show', compact('project', 'amenityDisplayGroups', 'topAmenities'));
    }
}
