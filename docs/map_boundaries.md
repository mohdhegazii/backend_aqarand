# Admin Map Boundaries Feature

This feature allows administrators to define polygon boundaries for Countries, Regions, Cities, Districts, and Projects, and view them all on a unified, layered map.

## 1. Database Changes
A new column `boundary_polygon` (JSON, nullable) has been added to the following tables:
- `countries`
- `regions`
- `cities`
- `districts`
- `projects`

This column stores the GeoJSON representation of the boundary.

## 2. Editing Boundaries
In the Admin Dashboard, the Create and Edit forms for all the above entities now include a **Boundary Polygon** map widget.

- **Draw**: Use the pentagon icon in the map toolbar to draw a polygon.
- **Edit**: Use the edit icon to modify an existing polygon.
- **Delete**: Use the trash icon to remove the polygon.
- **Save**: The polygon is saved automatically when you submit the form.

## 3. Global Map View
A new page is available at `/admin/map/boundaries`.
This page renders all active entities with defined boundaries on a single map.

### Layering (Z-Index)
Layers are stacked from bottom to top:
1.  **Country** (Bottom) - Deep Blue
2.  **Region** - Teal
3.  **City** - Orange
4.  **District** - Purple
5.  **Project** (Top) - Red

### Legend
A legend at the bottom right of the map indicates the color coding for each entity type.

## 4. Technical Details
- **Frontend**: Uses Leaflet.js and Leaflet Draw (via CDN).
- **Backend**:
    - `Admin\MapController@boundaries` fetches the data.
    - Entities are rendered using Leaflet Panes to ensure correct Z-ordering regardless of load order.
    - Data is stored as standard GeoJSON.

## 5. Deployment
- Run `php artisan migrate` to add the `boundary_polygon` columns.
- Ensure the `App\Models` have the `boundary_polygon` cast to `array`.
