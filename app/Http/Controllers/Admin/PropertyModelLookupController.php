<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyModel;
use Illuminate\Http\Request;

class PropertyModelLookupController extends Controller
{
    /**
     * Search for property models.
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
        ]);

        $query = $validated['q'] ?? null;
        $limit = $validated['limit'] ?? 20;

        $modelsQuery = PropertyModel::query()
            ->with(['project:id,name_en,name_ar']) // Optional: include project name
            ->select('id', 'name_en', 'name_ar', 'project_id', 'code')
            ->when($query, function ($q) use ($query) {
                $like = "%{$query}%";
                $q->where(function($sub) use ($like) {
                    $sub->where('name_en', 'like', $like)
                        ->orWhere('name_ar', 'like', $like)
                        ->orWhere('code', 'like', $like);
                });
            })
            ->when($request->filled('project_id'), function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            })
            ->limit($limit);

        $models = $modelsQuery->orderBy('name_en')->get();

        $results = $models->map(function ($model) {
            return [
                'id' => $model->id,
                'text' => $model->name_en ?? $model->name_ar,
                'name' => $model->name_en ?? $model->name_ar,
            ];
        });

        return response()->json($results);
    }
}
