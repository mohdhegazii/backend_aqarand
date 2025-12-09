# Project Admin UI Implementation (Arabic)

## Overview
This document outlines the implementation of the Arabic Admin Interface for the Projects module. The implementation focuses on a 5-step wizard, media management, and location handling.

## Files Changed
- `app/Http/Controllers/Admin/LocationHelperController.php`: Added `lat` and `lng` to search results.
- `app/Http/Controllers/Admin/ProjectController.php`: Updated `store` and `update` logic to support the new form structure and media handling.
- `app/Services/ProjectMediaService.php`: Created to handle image processing (Intervention Image), watermarking, WebP conversion, and PDF storage.
- `resources/views/admin/projects/create.blade.php`: Updated to use the new form partial.
- `resources/views/admin/projects/edit.blade.php`: Updated to use the new form partial.
- `resources/views/admin/projects/index.blade.php`: Ensured Arabic labels and hidden Status/Delivery Year columns.
- `resources/views/admin/projects/partials/form.blade.php`: New partial containing the core 5-step wizard logic.

## 5-Step Wizard
The form is implemented using Alpine.js (`projectForm` component) in `partials/form.blade.php`.
- **Step 1: المعلومات الأساسية**: Basic info, Unified Location Search, Cascading Dropdowns, Map (Leaflet) with Polygon Draw.
- **Step 2: تفاصيل العقار والسعر**: Price, Area, Unit counts, Amenities.
- **Step 3: وصف العقار**: Long description and SEO fields.
- **Step 4: الفيديوهات والصور**: Media upload (Hero, Gallery, Brochure).
- **Step 5: النشر**: Active status and Save button.

## Media Management
### Gallery JSON Structure
The `gallery` column in the `projects` table stores a JSON array of objects:
```json
[
  {
    "path": "projects/1/gallery/gallery_1_123456_abc.webp",
    "name": "Image Name",
    "alt": "Alt Text",
    "is_hero_candidate": false
  }
]
```

### Hero Image Selection
- Users can upload a new Hero Image (replaced immediately).
- Users can also select an existing gallery image as the Hero Image via a radio button in the gallery grid.
- The `hero_image_url` column stores the relative path string.

### PDF Brochure Storage
- **Path**: `storage/app/public/projects/{project_id}/brochures/`
- **Database**: `brochure_url` stores the relative path (e.g., `projects/1/brochures/brochure_123.pdf`).

## Map & Polygon
- **Library**: Leaflet + Leaflet Draw.
- **Storage**: `map_polygon` column (JSON).
- **Behavior**: Drawing a polygon updates the hidden input with GeoJSON geometry. It also auto-calculates the center `lat` / `lng` and updates those fields.

## Location Search
- Uses `LocationHelperController` to search for Country/Region/City/District.
- On selection, populates the hidden IDs and triggers cascading fetches to populate dropdowns.
- Centers the map on the selected location's coordinates.

## 2024-10 Location Stability Fixes
- Country is now fixed to the default (Egypt) on the project wizard and hidden from the form to prevent accidental changes.
- District selection is optional; validation allows null districts while still enforcing hierarchy when provided.
- Cascading dropdowns were hardened to re-fetch regions/cities/districts on every change, avoiding empty/disabled states after country switches.
- The map now auto-loads the boundary polygon for the chosen district/city/region (with district > city > region precedence) using the unified location polygon service.
