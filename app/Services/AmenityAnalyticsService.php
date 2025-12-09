<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Amenity;

class AmenityAnalyticsService
{
    /**
     * Get the top amenities used in projects.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopProjectAmenities(int $limit = 10): Collection
    {
        return Cache::remember('dashboard.top_project_amenities', 900, function () use ($limit) {
            $stats = DB::table('project_amenity')
                ->select('amenity_id', DB::raw('count(*) as projects_count'))
                ->groupBy('amenity_id')
                ->orderByDesc('projects_count')
                ->limit($limit)
                ->get();

            if ($stats->isEmpty()) {
                return collect();
            }

            $amenityIds = $stats->pluck('amenity_id')->toArray();
            $amenities = Amenity::whereIn('id', $amenityIds)->get()->keyBy('id');

            return $stats->map(function ($stat) use ($amenities) {
                $amenity = $amenities->get($stat->amenity_id);
                return (object) [
                    'id' => $stat->amenity_id,
                    'name' => $amenity ? $amenity->getDisplayNameAttribute() : 'Unknown',
                    'projects_count' => $stat->projects_count,
                    'icon_class' => $amenity ? $amenity->icon_class : null,
                ];
            });
        });
    }

    /**
     * Get the top amenities used in units (optional).
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopUnitAmenities(int $limit = 10): Collection
    {
        return Cache::remember('dashboard.top_unit_amenities', 900, function () use ($limit) {
            $stats = DB::table('unit_amenity')
                ->select('amenity_id', DB::raw('count(*) as units_count'))
                ->groupBy('amenity_id')
                ->orderByDesc('units_count')
                ->limit($limit)
                ->get();

             if ($stats->isEmpty()) {
                return collect();
            }

            $amenityIds = $stats->pluck('amenity_id')->toArray();
            $amenities = Amenity::whereIn('id', $amenityIds)->get()->keyBy('id');

            return $stats->map(function ($stat) use ($amenities) {
                $amenity = $amenities->get($stat->amenity_id);
                return (object) [
                    'id' => $stat->amenity_id,
                    'name' => $amenity ? $amenity->getDisplayNameAttribute() : 'Unknown',
                    'units_count' => $stat->units_count,
                    'icon_class' => $amenity ? $amenity->icon_class : null,
                ];
            });
        });
    }

    /**
     * Clear amenity analytics cache.
     * Should be called when amenities are attached/detached or projects/units created/deleted.
     */
    public function clearCache()
    {
        Cache::forget('dashboard.top_project_amenities');
        Cache::forget('dashboard.top_unit_amenities');
    }
}
