<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeLookupController extends Controller
{
    /**
     * Search for unit types.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['q'] ?? null;
        $limit = $validated['limit'] ?? 20;

        $typesQuery = UnitType::query()
            ->select('id', 'name_en', 'name_ar')
            ->when($query, function ($q) use ($query) {
                $like = "%{$query}%";
                $q->where(function($sub) use ($like) {
                    $sub->where('name_en', 'like', $like)
                        ->orWhere('name_ar', 'like', $like);
                });
            })
            ->limit($limit);

        $types = $typesQuery->orderBy('name_en')->get();

        $results = $types->map(function ($type) {
            return [
                'id' => $type->id,
                'text' => $type->name_en ?? $type->name_ar,
                'name' => $type->name_en ?? $type->name_ar,
            ];
        });

        return response()->json($results);
    }
}
