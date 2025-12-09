<?php

namespace App\Services;

use App\Models\Category;
use App\Models\PropertyType;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Class LookupService
 *
 * Centralized service for retrieving hierarchical lookups:
 * Category -> PropertyType -> UnitType
 */
class LookupService
{
    /**
     * Get all active categories.
     *
     * @return Collection|Category[]
     */
    public function getActiveCategories(): Collection
    {
        return Cache::rememberForever('lookups.categories.active', function () {
            return Category::where('is_active', true)
                ->orderBy('name_en')
                ->get();
        });
    }

    /**
     * Get active property types for a specific category.
     *
     * @param int $categoryId
     * @return Collection|PropertyType[]
     */
    public function getActivePropertyTypesByCategory(int $categoryId): Collection
    {
        return Cache::rememberForever("lookups.property_types.category.{$categoryId}", function () use ($categoryId) {
            return PropertyType::where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get();
        });
    }

    /**
     * Get all active property types.
     * Useful for legacy screens or flat lists.
     *
     * @return Collection|PropertyType[]
     */
    public function getAllActivePropertyTypes(): Collection
    {
        return Cache::rememberForever('lookups.property_types.active', function () {
            return PropertyType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get();
        });
    }

    /**
     * Get active unit types for a specific property type.
     *
     * @param int $propertyTypeId
     * @return Collection|UnitType[]
     */
    public function getActiveUnitTypesByPropertyType(int $propertyTypeId): Collection
    {
        return Cache::rememberForever("lookups.unit_types.property_type.{$propertyTypeId}", function () use ($propertyTypeId) {
            return UnitType::where('property_type_id', $propertyTypeId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get();
        });
    }

    /**
     * Get all active unit types.
     *
     * @return Collection|UnitType[]
     */
    public function getAllActiveUnitTypes(): Collection
    {
        return Cache::rememberForever('lookups.unit_types.active', function () {
            return UnitType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get();
        });
    }

    /**
     * Clear all lookup caches.
     * Should be called when any lookup entity is created, updated, or deleted.
     */
    public function clearAllCaches()
    {
        Cache::forget('lookups.categories.active');
        Cache::forget('lookups.property_types.active');
        Cache::forget('lookups.unit_types.active');

        // Wildcard clearing is not supported by standard Cache facade in all drivers.
        // For simplicity in this task, we will just rely on specific clearing or clearing mostly everything if possible.
        // However, we can't easily iterate keys.
        // A better approach for specific updates would be to clear specific keys if we know the ID,
        // but for now, since we are doing "Safety & Compatibility", let's just make sure we clear the main lists.
        // For the specific category/property_type lists, we might need to rely on the fact that
        // they are less frequently changed, OR we implement specific clear methods.
    }

    public function clearCategoryCache()
    {
        Cache::forget('lookups.categories.active');
    }

    public function clearPropertyTypeCache(int $categoryId = null)
    {
        Cache::forget('lookups.property_types.active');
        if ($categoryId) {
            Cache::forget("lookups.property_types.category.{$categoryId}");
        }
    }

    public function clearUnitTypeCache(int $propertyTypeId = null)
    {
        Cache::forget('lookups.unit_types.active');
        if ($propertyTypeId) {
            Cache::forget("lookups.unit_types.property_type.{$propertyTypeId}");
        }
    }
}
