# Project Module Update (Arabic Admin & Media Handling)

## Overview
The Project module has been overhauled to provide a full Arabic interface, enhanced media management, and map polygon tools.

## Key Changes
1. **Localization**: All labels and UI elements in the Project Admin (List, Create, Edit) are now in Arabic.
2. **Hidden Fields**: `status` and `delivery_year` are hidden from the UI forms but preserved in the database.
3. **Map & Polygon**:
   - Added `map_polygon` JSON column to `projects` table.
   - Integrated Leaflet Draw to allow drawing project boundaries.
   - Map automatically centers on the polygon.
4. **Media Management**:
   - **Service**: `App\Services\ProjectMediaService` handles all uploads.
   - **Hero Image**: Can be uploaded directly or selected from the gallery. Stored in `projects/{id}/hero/`.
   - **Gallery**: Multiple images, optimized to WebP, watermarked. Metadata (name, alt) stored in JSON. Stored in `projects/{id}/gallery/`.
   - **Brochure**: PDF uploads stored in `projects/{id}/brochures/`.
5. **Location Logic**:
   - Unified search input triggers cascading dropdowns (Country > Region > City > District).

## Technical Details
- **Media Paths**: Relative paths stored in DB. Files in `storage/app/public/`.
- **Watermark**: `storage/app/watermarks/project_watermark.png` is applied to bottom-right of images.
- **Frontend**: Uses Alpine.js for multi-step wizard and location logic. Leaflet.js for maps.
