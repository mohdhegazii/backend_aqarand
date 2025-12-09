<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitLookupController extends Controller
{
    /**
     * Search for units.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
            'project_id' => 'nullable|integer',
            'status' => 'nullable|string', // available, etc
            'doesnt_have_listing' => 'nullable|boolean',
        ]);

        $query = $validated['q'] ?? null;
        $limit = $validated['limit'] ?? 20;

        $unitsQuery = Unit::query()
            ->with(['project:id,name_en,name_ar']) // Eager load project name
            ->select('id', 'unit_number', 'project_id', 'price', 'currency_code', 'unit_status')
            ->when($query, function ($q) use ($query) {
                $like = "%{$query}%";
                $q->where('unit_number', 'like', $like);
            })
            ->when($request->filled('project_id'), function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('unit_status', $request->status);
            })
            ->when($request->boolean('doesnt_have_listing'), function ($q) {
                 $q->doesntHave('listing');
            })
            ->limit($limit);

        $units = $unitsQuery->latest()->get();

        $results = $units->map(function ($unit) {
            $projectName = $unit->project ? ($unit->project->name_en ?? $unit->project->name_ar) : 'No Project';
            return [
                'id' => $unit->id,
                'text' => "{$unit->unit_number} - {$projectName} ({$unit->unit_status})",
                'unit_number' => $unit->unit_number,
                'project_name' => $projectName,
                'price_formatted' => number_format($unit->price) . ' ' . $unit->currency_code,
            ];
        });

        return response()->json($results);
    }
}
