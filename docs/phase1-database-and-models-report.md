# Phase 1: Database wiring + Eloquent models + relationships

## Summary

In this phase, I have implemented the Eloquent models and relationships for the core real estate platform, adhering to the provided database schema without modifying migrations or the schema file.

The work includes:
-   Creating Eloquent models for all core tables.
-   Defining relationships (One-to-Many, Many-to-Many, One-to-One).
-   Implementing query scopes for common filtering scenarios.
-   Adding appropriate casts for JSON fields and boolean flags.

## List of Models Created

The following models have been created in `app/Models/`:

1.  **Location Models:**
    *   `Country`
    *   `Region`
    *   `City`
    *   `District`
2.  **Core Entities:**
    *   `Developer`
    *   `Project`
    *   `PropertyModel` (Unit Models)
    *   `Unit`
    *   `Listing`
3.  **Metadata/Types:**
    *   `PropertyType`
    *   `UnitType`
    *   `Amenity`

## Relationships Diagram (Textual)

*   **Country**
    *   `hasMany` Region
*   **Region**
    *   `belongsTo` Country
    *   `hasMany` City
*   **City**
    *   `belongsTo` Region
    *   `hasMany` District
*   **District**
    *   `belongsTo` City
    *   `hasMany` Project
*   **Developer**
    *   `hasMany` Project
*   **Project**
    *   `belongsTo` Developer, Country, Region, City, District
    *   `hasMany` PropertyModel, Unit
    *   `belongsToMany` Amenity (via `project_amenity`)
*   **PropertyModel**
    *   `belongsTo` Project, UnitType
    *   `hasMany` Unit
*   **PropertyType**
    *   `hasMany` UnitType
*   **UnitType**
    *   `belongsTo` PropertyType
    *   `hasMany` PropertyModel, Unit
*   **Unit**
    *   `belongsTo` Project, PropertyModel, UnitType
    *   `hasOne` Listing
*   **Listing**
    *   `belongsTo` Unit

## Technical Decisions

1.  **JSON Fields:**
    *   Used `$casts` to automatically convert JSON columns to arrays.
    *   Examples: `Project::$casts['gallery' => 'array']`, `Unit::$casts['equipment' => 'array']`.

2.  **Decimals:**
    *   Used `decimal:2` (or similar) casting for price and area fields to ensure precision when working with these values in PHP.

3.  **Booleans:**
    *   Fields like `is_active`, `is_featured`, `is_furnished` are cast to `boolean` for easier logic checks.

4.  **Timestamps:**
    *   Standard `created_at` and `updated_at` are enabled by default on all models as they are present in the schema.

5.  **Pivot Table:**
    *   The `project_amenity` table is handled via `belongsToMany` relationships in `Project` and `Amenity` models. No separate Pivot model was needed for this phase as there are no extra attributes on the pivot table worth managing explicitly yet.

## How to Use the Models

### Load a Project with Units and Amenities

```php
use App\Models\Project;

$project = Project::with(['units', 'amenities'])->find(1);

foreach ($project->units as $unit) {
    echo $unit->unit_number . ' - ' . $unit->price;
}

foreach ($project->amenities as $amenity) {
    echo $amenity->name_en;
}
```

### Load a Listing with Unit and Project Details

```php
use App\Models\Listing;

$listing = Listing::with(['unit.project.developer'])->first();

echo $listing->title;
echo $listing->unit->price;
echo $listing->unit->project->name;
echo $listing->unit->project->developer->name;
```

### Query Published Primary Listings in a specific Price Range

```php
use App\Models\Listing;

// Using the scopes defined in Listing and Unit
$listings = Listing::scopePublished()
    ->scopePrimary()
    ->whereHas('unit', function($q) {
        $q->scopeAvailable()
          ->scopeByPriceRange(1000000, 5000000);
    })
    ->get();
```

## Next Recommendations

For Phase 2, I recommend:

1.  **Admin CRUD:** Implement Filament or standard Laravel controllers for managing these resources, especially Locations and Types which are foundational.
2.  **Validation:** Add FormRequests for creating/updating these entities, ensuring rules match database constraints (e.g., unique slugs).
3.  **Factories & Seeders:** Create factories and seeders to populate the database with test data, which will facilitate frontend development and more robust testing.
4.  **Authentication:** Set up User model and authentication (API/Web) if not already present.
