# Project Polygon Usage Analysis

## `map_polygon`
- **Migrations**: Added in `2027_01_06_000006_add_map_polygon_to_projects.php` and `2027_01_06_000007_add_missing_project_columns.php`.
- **Model**: Cast as `array` in `Project.php`.
- **Controller Usage**:
  - `LocationPolygonController.php`: Used to fetch project polygons for the admin map.
  - `LocationHelperController.php`: Included in project search results.
- **Views**:
  - `resources/views/admin/projects/steps/step1_basic_location.blade.php`: Used in what appears to be a legacy or alternative step view.

## `project_boundary_geojson`
- **Migrations**: Added in `2027_01_07_000000_add_project_boundary_geojson_to_projects_table.php` (one day after `map_polygon`).
- **Model**: Cast as `array` in `Project.php`.
- **Controller Usage**:
  - `ProjectWizardController.php`: Explicitly used in validation and storage logic.
- **Views**:
  - `resources/views/admin/projects/steps/basics.blade.php`: The current active Wizard Step 1 view uses this field.

## Conclusion
The system currently writes to `project_boundary_geojson` in the Wizard but reads from `map_polygon` in the global map (`LocationPolygonController`). This means new projects created via the Wizard might not show up on the global map unless these columns are synced. Normalization is required.
