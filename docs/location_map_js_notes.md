# Location Map JS Refactor Notes

**Date:** 2027-01-20
**Task:** 8 — Refactor & Optimize `location-map.js`

## 1. Analysis of Original Code

The original `location-map.js` was a functional but monolithic script.
- **Global Pollution:** It attached logic directly to `window.initLocationMap` without encapsulation, potentially exposing internal variables if not careful (though the original was also in an IIFE).
- **Performance Issue (Rendering):** It iterated through every single polygon returned by the API and created a separate `L.geoJSON` layer for *each* feature. For a map with hundreds of districts, this meant hundreds of DOM elements and Leaflet layer instances, causing significant overhead.
- **Performance Issue (Network):** It fetched the `/admin/location-polygons` endpoint every time `initLocationMap` was called. If multiple maps were present or if the function was re-invoked, it would trigger redundant network requests.
- **Structure:** All logic (map setup, marker, drawing, fetching, searching) was mixed in one large function, making it hard to read and maintain.

## 2. Refactoring Strategy

The refactor focused on modularity and performance without breaking the public API.

### Modularity
- **IIFE:** The entire module is wrapped in an Immediately Invoked Function Expression to keep internal helpers private.
- **Helper Functions:** Logic was broken down into distinct functions:
  - `createMap()`: Handles Leaflet initialization and bounds.
  - `setupMarkerLogic()`: Handles point markers and input updates.
  - `setupDrawLogic()`: Handles the editable polygon and Leaflet Draw events.
  - `loadContextLayers()`: Handles fetching and rendering background polygons.
  - `resolveApiPath()`: Centralizes URL generation logic.

### Performance Improvements
- **Layer Grouping:** Instead of adding 100 separate layers for 100 districts, the code now groups them by level (e.g., all districts) and creates a **single** `L.geoJSON` layer containing a `FeatureCollection`. This drastically reduces Leaflet's processing time and DOM manipulation.
- **Request Caching:** A simple in-memory cache (`_polygonCache`) stores the promise of the fetch request. Subsequent calls with the same parameters reuse the existing promise, eliminating redundant network calls on the same page load.
- **Efficient Filtering:** Filtering of the "current entity" happens before layer creation, preventing unnecessary processing.

## 3. Configuration & API
The public API remains exactly the same:
```javascript
window.initLocationMap({
    elementId: 'map',
    entityLevel: 'city',
    // ... other options
});
```
Internal options normalization ensures robust default values.

## 4. Safety
- **Backward Compatibility:** `window['map_' + id]` and `window.updateLocationMapBoundary` are still exposed as expected by legacy Blade templates.
- **Localization:** The API path resolver handles URL prefixes (e.g., `/en/admin/`) correctly.
