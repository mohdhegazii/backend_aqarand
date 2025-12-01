# Phase 1 Database and Models Technical Report

## 1. Overview
This report validates the implementation of Phase 1 of the Aqar-and Real Estate Platform backend. The review was conducted on the latest `main` branch.
The goal of Phase 1 was to establish the core database schema, Eloquent models, and relationships, with a focus on multilingual readiness (Arabic & English).

**Review Status:** ✅ Phase 1 is complete and consistent with the specification.
**Environment Note:** The review was conducted via static analysis as the environment lacked a PHP binary for runtime testing. However, the code structure and logic have been rigorously checked against the schema.

## 2. Implemented Models

All models are located in `app/Models` and map correctly to the `database/sql/schema.sql`.

| Model | Table | Key Relationships |
| :--- | :--- | :--- |
| **Country** | `countries` | hasMany Regions |
| **Region** | `regions` | belongsTo Country, hasMany Cities |
| **City** | `cities` | belongsTo Region, hasMany Districts |
| **District** | `districts` | belongsTo City, hasMany Projects |
| **Developer** | `developers` | hasMany Projects |
| **PropertyType** | `property_types` | hasMany UnitTypes |
| **UnitType** | `unit_types` | belongsTo PropertyType, hasMany PropertyModels, hasMany Units |
| **Amenity** | `amenities` | belongsToMany Projects (via `project_amenity`) |
| **Project** | `projects` | belongsTo Developer, Locations, hasMany PropertyModels, hasMany Units |
| **PropertyModel** | `property_models` | belongsTo Project, UnitType, hasMany Units |
| **Unit** | `units` | belongsTo Project, PropertyModel, UnitType, hasOne Listing |
| **Listing** | `listings` | belongsTo Unit |

## 3. Relationships Summary

The following relationships have been verified and implemented:

*   **Locations Hierarchy:**
    *   Country -> Region -> City -> District.
    *   Each level correctly implements `hasMany` (down) and `belongsTo` (up).

*   **Project Relationships:**
    *   `Project` belongs to `Developer`.
    *   `Project` belongs to `Country`, `Region`, `City`, `District`.
    *   `Project` has many `PropertyModel`s and `Unit`s.
    *   `Project` belongs to many `Amenity`s (Pivot: `project_amenity`).

*   **Unit & Property Model Hierarchy:**
    *   `PropertyModel` belongs to `Project` and `UnitType`.
    *   `Unit` belongs to `Project`, `PropertyModel`, and `UnitType`.
    *   `UnitType` belongs to `PropertyType`.

*   **Listings:**
    *   `Listing` belongs to `Unit`.

## 4. Query Scopes Summary

### Listing Scopes
*   `scopePublished`: Filters listings with `status = 'published'`.
*   `scopePrimary`: Filters listings with `listing_type = 'primary'`.
*   `scopeResale`: Filters listings with `listing_type = 'resale'`.
*   `scopeRental`: Filters listings with `listing_type = 'rental'`.

### Unit Scopes
*   `scopeAvailable`: Filters units with `unit_status = 'available'`.
*   `scopeByPriceRange($min, $max)`: Filters units by price range.

## 5. Multilingual Readiness (Arabic / English / Future Languages)

The platform supports Arabic and English out of the box for metadata tables, with plans for future expansion.

### Current Implementation (Phase 1)
*   **Dual-Column Strategy:** The following tables use `name_en` and `name_local` columns:
    *   `countries`
    *   `regions`
    *   `cities`
    *   `districts`
    *   `property_types`
    *   `amenities`

    This allows for immediate, performant support for the two primary languages (English and Arabic). `name_local` stores the Arabic name.

*   **Helper Methods:**
    A `getName($locale = null)` method has been added to the above models.
    *   If `$locale` is 'ar', it returns `name_local` (falling back to `name_en`).
    *   Otherwise, it returns `name_en`.
    *   If `$locale` is not provided, it uses `app()->getLocale()`.

*   **Single-Language Entities:**
    *   `projects`, `property_models`, `developers`, `unit_types`, `listings` currently use a single `name` or `title` column. These are treated as language-neutral or primary language (usually English or mixed) for Phase 1.

### Future Extension Strategy
To support more languages (French, German, etc.) in the future:
1.  **Translation Tables:** Move from `name_en`/`name_local` columns to separate translation tables (e.g., `country_translations`, `project_translations`) using a package like `spatie/laravel-translatable` or standard Laravel localization patterns.
2.  **JSON Columns:** Alternatively, convert name columns to JSON (e.g., `{"en": "Name", "ar": "الاسم"}`).
3.  **Slugs:** Implement localized slugs in translation tables to support SEO in multiple languages (e.g., `/en/project/sunset` vs `/ar/project/ghoroub`).

## 6. Issues Found & Fixes Applied

*   **Multilingual Helpers Missing:** The initial Phase 1 code lacked convenient accessors for localized names.
    *   *Fix:* Added `getName($locale = null)` method to `Country`, `Region`, `City`, `District`, `PropertyType`, and `Amenity` models to abstract the logic of choosing `name_en` vs `name_local`.

## 7. Final Status of Phase 1

✅ **Phase 1 is complete and consistent with the specification.**
The database schema is correctly mapped to Eloquent models, relationships are established, and the foundation for multilingual support is in place.
