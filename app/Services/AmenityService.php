<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\AmenityCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class AmenityService
{
    /**
     * Cache key for active amenity categories.
     */
    const CACHE_KEY_CATEGORIES_ACTIVE = 'amenity_categories.active';

    /**
     * Cache key prefix for grouped amenities.
     */
    const CACHE_KEY_AMENITIES_GROUPED_PREFIX = 'amenities.grouped.';

    /**
     * Get all amenity categories.
     *
     * @param bool $onlyActive
     * @return Collection
     */
    public function getCategories(bool $onlyActive = true): Collection
    {
        if ($onlyActive) {
            return Cache::remember(self::CACHE_KEY_CATEGORIES_ACTIVE, 3600, function () {
                return AmenityCategory::where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name_en')
                    ->get();
            });
        }

        return AmenityCategory::orderBy('sort_order')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get amenities filtered by type.
     *
     * @param string|null $type 'project', 'unit', 'both', or null for all.
     * @param bool $onlyActive
     * @return Collection
     */
    public function getAmenitiesByType(string $type = null, bool $onlyActive = true): Collection
    {
        $query = Amenity::query();

        if ($type) {
            // If type is 'project', we usually want 'project' AND 'both' amenities.
            // If type is 'unit', we want 'unit' AND 'both'.
            // However, the specific requirement might be strict filtering.
            // Based on ProjectController usage: whereIn('amenity_type', ['project', 'both'])

            if ($type === 'project') {
                $query->whereIn('amenity_type', ['project', 'both']);
            } elseif ($type === 'unit') {
                $query->whereIn('amenity_type', ['unit', 'both']);
            } elseif ($type === 'both') {
                $query->where('amenity_type', 'both');
            } else {
                 // If exact match is needed or if $type matches DB column value exactly
                 // But strictly speaking, 'project' usually implies project-compatible amenities.
            }
        }

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->with('category')
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get amenities grouped by category.
     *
     * @param string|null $type 'project', 'unit', 'both', or null.
     * @param bool $onlyActive
     * @return Collection
     */
    public function getAmenitiesGroupedByCategory(string $type = null, bool $onlyActive = true): Collection
    {
        $cacheKey = self::CACHE_KEY_AMENITIES_GROUPED_PREFIX . ($type ?? 'all') . '.' . ($onlyActive ? 'active' : 'all');

        $callback = function () use ($type, $onlyActive) {
            $amenities = $this->getAmenitiesByType($type, $onlyActive);

            // Group by category name or object?
            // Usually grouping by relation id or name is standard.
            // Let's group by category Name (English) for now as that provides a labeled collection.
            // Or better, return the Categories with their Amenities loaded, which is cleaner for iteration.

            // However, the prompt says "Returns a collection keyed by category".
            // If we use $amenities->groupBy('category.name_en'), we get a collection of amenities keyed by string.
            // If we use $amenities->groupBy('category_id'), we get it keyed by ID.

            // A more robust approach often used in views:
            // Iterate over Categories, and for each category, show its amenities.
            // But this method is 'getAmenitiesGroupedByCategory'.

            // Let's look at how we might construct this.
            // If we want to return Categories that HAVE amenities of this type:

            $categories = $this->getCategories($onlyActive);
            $amenities = $this->getAmenitiesByType($type, $onlyActive);

            $grouped = new Collection();

            foreach ($categories as $category) {
                $categoryAmenities = $amenities->where('amenity_category_id', $category->id);
                if ($categoryAmenities->isNotEmpty()) {
                    // clone category to avoid modifying the cached instance if we were modifying it
                    // but here we are just setting a relation or attribute?
                    // Better to just map: Category -> Amenities

                    // To keep it simple and useful for views:
                    // We can stick the amenities into a temporary attribute on the category object
                    // OR return a collection: [ CategoryA => [Amenity1, Amenity2], ... ]
                    // Since keys in PHP arrays/collections can only be int or string, we can't key by Object.

                    // So we probably want:
                    // Collection of Categories, where each Category has a relation 'amenities' loaded and filtered.

                    // But 'getAmenitiesByType' returns a flat list.

                    // Let's manually hydrate the 'amenities' relation on the category objects?
                    // Or just return the flat list grouped by category name?

                    // Given the prompt: "Returns a collection keyed by category (or array of [category => amenities])."
                    // "Internally uses getAmenitiesByType() and AmenityCategory relation."

                    // Let's use the groupBy on the amenities collection, but using the category object is not possible as key.
                    // Let's group by `amenity_category_id`.
                    // But then we lose the Category name in the key.

                    // Alternative: Return Categories with filtered amenities.
                    // This is usually what views want: "Foreach Category... show Name... Foreach Amenity..."

                    // Let's try to match the likely expectation: A collection of Categories, each with a `filtered_amenities` property or relation.
                    // OR: A collection keyed by Category Name.

                    // Let's go with: Collection of AmenityCategory objects, where `amenities` relation is set to the filtered list.
                    $cat = clone $category;
                    $cat->setRelation('amenities', $categoryAmenities->values());
                    $grouped->push($cat);
                }
            }

            return $grouped;
        };

        if ($onlyActive) {
            return Cache::remember($cacheKey, 3600, $callback);
        }

        return $callback();
    }

    /**
     * Clear all amenity-related caches.
     * Should be called when Amenity or AmenityCategory is updated.
     */
    public function clearCache()
    {
        Cache::forget(self::CACHE_KEY_CATEGORIES_ACTIVE);

        // We need to clear all variations of grouped keys.
        // Since we can't easily wildcard delete in all cache drivers (like file),
        // we should list known variations or tag them if tags were available (but might not be).
        // For simple usage:
        $types = ['project', 'unit', 'both', 'all', null];
        $states = ['active', 'all'];

        foreach ($types as $t) {
            $tStr = $t ?? 'all';
            foreach ($states as $s) {
                Cache::forget(self::CACHE_KEY_AMENITIES_GROUPED_PREFIX . $tStr . '.' . $s);
            }
        }
    }
}
