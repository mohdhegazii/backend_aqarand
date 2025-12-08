<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeveloperService;
use Illuminate\Http\Request;

class DeveloperLookupController extends Controller
{
    /**
     * Search for active developers for use in lookups/dropdowns.
     *
     * @param Request $request
     * @param DeveloperService $developerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, DeveloperService $developerService)
    {
        // Validate input parameters
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['q'] ?? null;
        $limit = $validated['limit'] ?? 20;

        // Perform search using the service
        $developers = $developerService->searchActiveDevelopers($query, $limit);

        // Transform results to a minimal structure
        $results = $developers->map(function ($developer) {
            return [
                'id' => $developer->id,
                'name' => $developer->display_name, // Accessor
                'logo_url' => $developer->logo_url, // Accessor
            ];
        });

        return response()->json($results);
    }
}
