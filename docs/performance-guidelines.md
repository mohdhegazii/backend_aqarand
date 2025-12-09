# Performance Guidelines

This document outlines the performance strategy for the backend application, including recent index additions, query best practices, and search optimization plans.

## 1. Added Indexes

As of May 2027 (`2027_05_12_000000_add_performance_indexes.php`), several indexes have been added to optimize critical read paths.

| Table | Index Name | Columns | Purpose |
| :--- | :--- | :--- | :--- |
| `projects` | `projects_lat_lng_index` | `lat`, `lng` | Optimizes viewport bounding box queries (e.g. `whereBetween`). |
| `projects` | `projects_city_id_index` | `city_id` | Speeds up filtering projects by city. |
| `projects` | `projects_district_id_index` | `district_id` | Speeds up filtering projects by district. |
| `units` | `units_price_built_up_area_index` | `price`, `built_up_area` | Optimizes unit filtering by price range and area. |
| `developers` | `developers_active_name_index` | `is_active`, `name` | Optimizes fetching active developers sorted by name (e.g. dropdowns). |
| `locations` | `*_boundary_spatial_index` | `boundary` | Enables efficient spatial filtering (`MBRIntersects`). *Requires NOT NULL column.* |

**Note on Spatial Indexes:**
While `locations_boundary_spatial_index` is crucial for map performance, it technically requires the `boundary` column to be `NOT NULL` in MySQL. Ensure this constraint is met in the schema to fully enable the index. The application logic relies on this index for efficient spatial queries.

## 2. General Query Rules

To avoid performance regressions, adhere to the following guidelines:

### A. Always Paginate Large Lists
Never use `Model::all()` or `->get()` without limits on tables that can grow indefinitely (Listings, Units, Projects). Use `->paginate()` or `->cursorPaginate()`.

### B. Database vs. Collection Sorting
Prefer sorting at the database level using indexed columns.
**Bad:** `User::all()->sortBy('name')` (Loads all records into memory, then sorts).
**Good:** `User::orderBy('name')->get()` (Sorts in DB, efficient if indexed).

### C. Select Only Needed Columns
When building APIs or massive lookups (e.g., map data), select specific columns to reduce memory usage and serialization overhead.
```php
// Good
Project::select('id', 'lat', 'lng', 'name_en')->get();
```

### D. Avoid N+1 Queries
Use Eager Loading (`with()`) when accessing relationships in loops.
```php
// Bad
foreach ($projects as $project) {
    echo $project->developer->name;
}

// Good
$projects = Project::with('developer')->get();
```

## 3. Search Strategy

Currently, search functionality (e.g., finding developers or projects by name) uses a multi-column `OR LIKE` pattern.

```php
$q->where(function($query) use ($term) {
    $query->where('name_en', 'like', "%$term%")
          ->orWhere('name_ar', 'like', "%$term%");
});
```

**Status:** This is a temporary solution. It prevents index usage on large datasets.
**Future Plan:** Move to Fulltext Search (MySQL Fulltext or a dedicated engine like Meilisearch/Elasticsearch) when table sizes grow beyond 100k rows.

## 4. Debugging Performance (EXPLAIN)

To understand why a query is slow, use `EXPLAIN`. You can run this directly in your database client, or verify the query plan in code during development.

### Helper Example
You can inspect the query plan by dumping the `explain()` output of a query builder instance:

```php
// in a Controller or test
$query = Project::where('is_active', 1)->whereBetween('lat', [20, 30]);

// Dump the execution plan
dd($query->explain());
```

Look for:
- **key**: The index being used. If `NULL`, no index is used (full table scan).
- **rows**: Estimate of rows examined.
- **type**: `ALL` means full table scan (bad for large tables). `range` or `ref` is better.
