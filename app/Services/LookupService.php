<?php

namespace App\Services;

use App\Models\Category;
use App\Models\PropertyType;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Collection;

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
        // TODO: Implement caching (e.g., Cache::rememberForever('active_categories', ...))
        return Category::where('is_active', true)
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get active property types for a specific category.
     *
     * @param int $categoryId
     * @return Collection|PropertyType[]
     */
    public function getActivePropertyTypesByCategory(int $categoryId): Collection
    {
        // TODO: Implement caching per category (e.g., Cache::rememberForever("property_types_cat_{$categoryId}", ...))
        return PropertyType::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get all active property types.
     * Useful for legacy screens or flat lists.
     *
     * @return Collection|PropertyType[]
     */
    public function getAllActivePropertyTypes(): Collection
    {
        // TODO: Implement caching (e.g., Cache::rememberForever('all_active_property_types', ...))
        return PropertyType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get active unit types for a specific property type.
     *
     * @param int $propertyTypeId
     * @return Collection|UnitType[]
     */
    public function getActiveUnitTypesByPropertyType(int $propertyTypeId): Collection
    {
        // TODO: Implement caching per property type (e.g., Cache::rememberForever("unit_types_pt_{$propertyTypeId}", ...))
        return UnitType::where('property_type_id', $propertyTypeId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get all active unit types.
     *
     * @return Collection|UnitType[]
     */
    public function getAllActiveUnitTypes(): Collection
    {
        // TODO: Implement caching (e.g., Cache::rememberForever('all_active_unit_types', ...))
        return UnitType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();
    }
}
