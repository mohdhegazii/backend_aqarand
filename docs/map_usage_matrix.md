# Map Usage Matrix & Refactor Plan

## Overview
This document tracks the usage of maps in the Admin panel and the plan to unify them under a single implementation (`public/js/admin/location-map.js` and `<x-location.map>`).

## Current Usage

| View | Purpose | Implementation | Status |
|------|---------|----------------|--------|
| `admin/countries/create.blade.php` | Edit Country Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/countries/edit.blade.php` | Edit Country Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/regions/create.blade.php` | Edit Region Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/regions/edit.blade.php` | Edit Region Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/cities/create.blade.php` | Edit City Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/cities/edit.blade.php` | Edit City Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/districts/create.blade.php` | Edit District Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/districts/edit.blade.php` | Edit District Boundary | `map_picker.blade.php` | 🔴 To Refactor |
| `admin/projects/steps/basics.blade.php` | Edit Project Location/Boundary | `location-map.js` (Inline Config) | 🔴 To Refactor |
| `admin/projects/create.blade.php` | Placeholder | Legacy/Empty | 🟢 Ignore |
| `admin/projects/edit.blade.php` | Placeholder | Legacy/Empty | 🟢 Ignore |
| `admin/partials/map_picker.blade.php` | Shared Partial | Mixed Inline/External | 🟡 To Replace |

## Refactor Plan

### 1. Create `<x-location.map>` Component
Create a reusable Blade component that encapsulates:
- Container div
- Hidden inputs (lat, lng, polygon)
- Read-only display (lat/lng text)
- Search bar (optional)
- Initialization script calling `initLocationMap`

### 2. Standardize Props
The component will accept:
- `$lat`, `$lng`, `$zoom`
- `$polygon` (GeoJSON)
- `$readOnly` (boolean)
- `$entityLevel` (string)
- `$entityId` (int)
- `$lockToEgypt` (boolean)
- `$searchable` (boolean)
- Input names mapping (`inputLatName`, etc.)
- `$mapId` (optional, defaults to generated ID)

### 3. Refactor Execution
- **Phase 1**: Create the component.
- **Phase 2**: Replace `map_picker` in Location CRUDs (Country, Region, City, District).
- **Phase 3**: Replace inline map in `projects/steps/basics.blade.php` with the component, ensuring custom callbacks (flyTo, updating specific inputs) work correctly.

## Notes
- `admin/projects/steps/basics.blade.php` has complex logic for cascading dropdowns that triggers map "flyTo". The component must expose the map instance or allow a callback to capture it. `location-map.js` already exposes `window['map_' + mapId]`, which satisfies this requirement.
