# Aqar-and Backend

Laravel backend for "Aqar-and" real estate listing platform.

## Setup

1.  Copy `.env.example` to `.env`.
2.  Install dependencies: `composer install`.
3.  Generate key: `php artisan key:generate`.
4.  Run migrations: `php artisan migrate`.
5.  Seed data: `php artisan db:seed`.

## Admin Access

*   URL: `/admin` (Redirects to `/login` if not authenticated)
*   Admin User: `admin@aqarand.test` / `password` (check DatabaseSeeder for actual credentials)

## Phase 3 - Advanced Real Estate Modules

Phase 3 introduces advanced real estate management capabilities, including Projects, Property Models, Units, and Listings, with full bilingual (English/Arabic) and SEO support.

### New Admin Resources

*   **Projects:** Manage real estate projects (compounds, developments).
    *   Route: `/admin/projects`
    *   Features: Bilingual names/descriptions/slugs, GEO location (Lat/Lng), Location hierarchy (Country->Region->City->District), Developer assignment, Amenities.
*   **Property Models:** Define unit models/types within a project (e.g., "Type A Villa").
    *   Route: `/admin/property-models`
    *   Features: Linked to Project & Unit Type, Area ranges, Price ranges, Floorplans.
*   **Units:** Individual physical units (inventory).
    *   Route: `/admin/units`
    *   Features: Specific unit number, price, status (available/sold/etc), specs (bedrooms, bathrooms, areas).
*   **Listings:** Publicly exposed offers for units.
    *   Route: `/admin/listings`
    *   Features: Primary/Resale/Rental, SEO titles/descriptions, Publishing status.

### Bilingual & SEO Fields

All core entities now support:
*   `name_en` / `name_ar` (or `title_en` / `title_ar`)
*   `description_en` / `description_ar`
*   `seo_slug_en` / `seo_slug_ar` (URL friendly slugs per language)
*   `meta_title_en` / `meta_title_ar`
*   `meta_description_en` / `meta_description_ar`

### GEO Readiness

*   Projects and Listings support spatial queries via `scopeInLocation($cityId, $districtId)`.
*   Latitude and Longitude fields available for Projects.

## Manual Migration Steps (No PHP Binary)

If you are running in an environment without PHP binary (to run artisan migrate):
1.  Import `database/sql/schema.sql`.
2.  The schema already contains `projects`, `property_models`, `units`, `listings` tables.
3.  **Run the following SQL** (derived from `database/migrations/2026_06_15_000000_add_bilingual_fields_phase_3.php`) to add the new Phase 3 columns:

```sql
-- Projects
ALTER TABLE projects ADD COLUMN name_en VARCHAR(200) NULL AFTER name;
ALTER TABLE projects ADD COLUMN name_ar VARCHAR(200) NULL AFTER name_en;
ALTER TABLE projects ADD COLUMN tagline_en VARCHAR(255) NULL AFTER tagline;
ALTER TABLE projects ADD COLUMN tagline_ar VARCHAR(255) NULL AFTER tagline_en;
ALTER TABLE projects ADD COLUMN description_en TEXT NULL AFTER description_long;
ALTER TABLE projects ADD COLUMN description_ar TEXT NULL AFTER description_en;
ALTER TABLE projects ADD COLUMN address_en VARCHAR(255) NULL AFTER address_text;
ALTER TABLE projects ADD COLUMN address_ar VARCHAR(255) NULL AFTER address_en;
ALTER TABLE projects ADD COLUMN seo_slug_en VARCHAR(220) NULL AFTER slug;
ALTER TABLE projects ADD COLUMN seo_slug_ar VARCHAR(220) NULL AFTER seo_slug_en;
ALTER TABLE projects ADD COLUMN meta_title_en VARCHAR(255) NULL AFTER meta_title;
ALTER TABLE projects ADD COLUMN meta_title_ar VARCHAR(255) NULL AFTER meta_title_en;
ALTER TABLE projects ADD COLUMN meta_description_en VARCHAR(255) NULL AFTER meta_description;
ALTER TABLE projects ADD COLUMN meta_description_ar VARCHAR(255) NULL AFTER meta_description_en;
CREATE UNIQUE INDEX projects_seo_slug_en_unique ON projects(seo_slug_en);
CREATE UNIQUE INDEX projects_seo_slug_ar_unique ON projects(seo_slug_ar);

-- Property Models
ALTER TABLE property_models ADD COLUMN name_en VARCHAR(200) NULL AFTER name;
ALTER TABLE property_models ADD COLUMN name_ar VARCHAR(200) NULL AFTER name_en;
ALTER TABLE property_models ADD COLUMN description_en TEXT NULL AFTER description;
ALTER TABLE property_models ADD COLUMN description_ar TEXT NULL AFTER description_en;
ALTER TABLE property_models ADD COLUMN seo_slug_en VARCHAR(220) NULL AFTER seo_slug;
ALTER TABLE property_models ADD COLUMN seo_slug_ar VARCHAR(220) NULL AFTER seo_slug_en;
ALTER TABLE property_models ADD COLUMN meta_title_en VARCHAR(255) NULL AFTER meta_title;
ALTER TABLE property_models ADD COLUMN meta_title_ar VARCHAR(255) NULL AFTER meta_title_en;
ALTER TABLE property_models ADD COLUMN meta_description_en VARCHAR(255) NULL AFTER meta_description;
ALTER TABLE property_models ADD COLUMN meta_description_ar VARCHAR(255) NULL AFTER meta_description_en;
CREATE UNIQUE INDEX property_models_seo_slug_en_unique ON property_models(seo_slug_en);
CREATE UNIQUE INDEX property_models_seo_slug_ar_unique ON property_models(seo_slug_ar);

-- Units
ALTER TABLE units ADD COLUMN title_en VARCHAR(255) NULL AFTER unit_number;
ALTER TABLE units ADD COLUMN title_ar VARCHAR(255) NULL AFTER title_en;
ALTER TABLE units ADD COLUMN meta_title_en VARCHAR(255) NULL AFTER media;
ALTER TABLE units ADD COLUMN meta_title_ar VARCHAR(255) NULL AFTER meta_title_en;
ALTER TABLE units ADD COLUMN meta_description_en VARCHAR(255) NULL AFTER meta_title_ar;
ALTER TABLE units ADD COLUMN meta_description_ar VARCHAR(255) NULL AFTER meta_description_en;

-- Listings
ALTER TABLE listings ADD COLUMN title_en VARCHAR(255) NULL AFTER title;
ALTER TABLE listings ADD COLUMN title_ar VARCHAR(255) NULL AFTER title_en;
ALTER TABLE listings ADD COLUMN slug_en VARCHAR(255) NULL AFTER slug;
ALTER TABLE listings ADD COLUMN slug_ar VARCHAR(255) NULL AFTER slug_en;
ALTER TABLE listings ADD COLUMN seo_title_en VARCHAR(255) NULL AFTER seo_title;
ALTER TABLE listings ADD COLUMN seo_title_ar VARCHAR(255) NULL AFTER seo_title_en;
ALTER TABLE listings ADD COLUMN seo_description_en VARCHAR(255) NULL AFTER seo_description;
ALTER TABLE listings ADD COLUMN seo_description_ar VARCHAR(255) NULL AFTER seo_description_en;
CREATE UNIQUE INDEX listings_slug_en_unique ON listings(slug_en);
CREATE UNIQUE INDEX listings_slug_ar_unique ON listings(slug_ar);
```
