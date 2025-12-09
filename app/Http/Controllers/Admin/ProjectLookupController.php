<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectLookupController extends Controller
{
    /**
     * Search for projects.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
            'developer_id' => 'nullable|integer',
        ]);

        $query = $validated['q'] ?? null;
        $limit = $validated['limit'] ?? 20;

        $projectsQuery = Project::query()
            ->select('id', 'name_en', 'name_ar', 'developer_id') // optimized select
            ->when($query, function ($q) use ($query) {
                $like = "%{$query}%";
                $q->where(function($sub) use ($like) {
                    $sub->where('name_en', 'like', $like)
                        ->orWhere('name_ar', 'like', $like);
                });
            })
            ->when($request->filled('developer_id'), function ($q) use ($request) {
                $q->where('developer_id', $request->developer_id);
            })
            ->limit($limit);

        // Sorting: exact match first (if possible), then alphabetical
        // Simple alphabetical for now
        $projects = $projectsQuery->orderBy('name_en')->get();

        $results = $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'text' => $project->name_en ?? $project->name_ar, // Standard 'text' property for generic use
                'name' => $project->name_en ?? $project->name_ar, // Legacy support
                'developer_id' => $project->developer_id,
            ];
        });

        return response()->json($results);
    }
}
