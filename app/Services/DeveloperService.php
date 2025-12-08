<?php

namespace App\Services;

use App\Models\Developer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DeveloperService
{
    /**
     * Get all active developers.
     *
     * @return Collection
     */
    public function getActiveDevelopers(): Collection
    {
        return Cache::remember('developers.active.list', now()->addMinutes(60), function () {
            // Fetch all active developers and sort by the display_name accessor.
            // sortBy returns a new collection, values() resets the keys.
            return Developer::where('is_active', true)
                ->get()
                ->sortBy(function ($developer) {
                    return strtolower($developer->display_name);
                }, SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        });
    }

    /**
     * Search active developers by name.
     *
     * @param string|null $query
     * @param int $limit
     * @return Collection
     */
    public function searchActiveDevelopers(?string $query = null, int $limit = 20): Collection
    {
        $dbQuery = Developer::where('is_active', true);

        if (!empty($query)) {
            $dbQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('name_en', 'like', "%{$query}%")
                  ->orWhere('name_ar', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('description_en', 'like', "%{$query}%")
                  ->orWhere('description_ar', 'like', "%{$query}%");
            });
        } else {
             // If no query, prioritize by name (fallback order)
             $dbQuery->orderBy('name');
        }

        return $dbQuery->limit($limit)->get();
    }

    /**
     * Find an active developer by ID.
     *
     * @param int $id
     * @return Developer|null
     */
    public function findActiveDeveloperById(int $id): ?Developer
    {
        return Developer::where('is_active', true)->find($id);
    }
}
