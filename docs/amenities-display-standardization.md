# Amenities Display Standardization

This document outlines the standard way to display amenities on the frontend for Projects and Units.

## Objective

- **Grouped by Category**: Amenities are organized under their respective categories (e.g., Services, Entertainment).
- **Ordered**: Categories and amenities are sorted by `sort_order`.
- **Icons**: Icons or images are displayed next to the amenity name.
- **Consistent**: The same logic is used across Project and Unit pages.

## Implementation Details

### 1. AmenityService

The `App\Services\AmenityService` class has been updated with two new methods:

#### `formatAmenitiesForDisplay(Collection $amenities): array`

Accepts a collection of `Amenity` models (with loaded `category` relation) and returns a structured array grouped by category.

**Structure:**
```php
[
    [
        'category_id' => 1,
        'category_name' => 'Services',
        'category_sort_order' => 1,
        'items' => [
            [
                'id' => 10,
                'name' => 'Swimming Pool',
                'icon_class' => 'fa fa-swimmer',
                'image_url' => '...'
            ],
            // ...
        ]
    ],
    // ...
]
```

#### `getTopAmenitiesForProject(Project $project, int $limit = 3): array`

Returns a flat array of the top N amenities for a project, sorted by `sort_order`. Useful for highlight sections.

### 2. Controller Usage

The following frontend controllers have been implemented and use `AmenityService` to format data:
- `App\Http\Controllers\Frontend\ProjectController`
- `App\Http\Controllers\Frontend\UnitController`

Routes:
- `GET /projects/{slug}` -> `ProjectController@show`
- `GET /units/{id}` -> `UnitController@show`

**Example Usage in Controller:**
```php
public function show(Project $project, AmenityService $amenityService)
{
    // Load amenities with category to optimize queries
    $projectAmenities = $project->amenities()
        ->where('is_active', true)
        ->with('category')
        ->get();

    $amenityDisplayGroups = $amenityService->formatAmenitiesForDisplay($projectAmenities);
    $topAmenities = $amenityService->getTopAmenitiesForProject($project, 3);

    return view('frontend.projects.show', compact('project', 'amenityDisplayGroups', 'topAmenities'));
}
```

### 3. Frontend Views

Standardized Blade partials are available in:
- `resources/views/frontend/projects/partials/amenities.blade.php`
- `resources/views/frontend/units/partials/amenities.blade.php`

The main show views utilize these partials:
- `resources/views/frontend/projects/show.blade.php`
- `resources/views/frontend/units/show.blade.php`

**Example Include:**
```blade
@include('frontend.projects.partials.amenities', [
    'amenityDisplayGroups' => $amenityDisplayGroups,
    'topAmenities' => $topAmenities ?? []
])
```
