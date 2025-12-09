/**
 * Location Map Module
 *
 * Handles map initialization, polygon rendering, and drawing tools for the admin panel.
 *
 * Refactor Notes (Task 8):
 * - Structure: Wrapped in IIFE to avoid global pollution.
 * - Performance:
 *   - Implements request caching to prevent redundant API calls on re-init.
 *   - Groups context polygons by level and renders them as single GeoJSON layers instead of individual layers per feature.
 * - Modularity: Broken down into helpers (initMap, loadContext, setupDraw, etc.).
 * - API: Preserves `window.initLocationMap(options)` signature.
 */

(function() {
    'use strict';

    // --- Constants & Configuration ---

    const LEVEL_CONFIG = {
        country:  { color: '#3388ff', zIndex: 400, opacity: 0.2 },
        region:   { color: '#28a745', zIndex: 410, opacity: 0.4 },
        city:     { color: '#fd7e14', zIndex: 420, opacity: 0.5 },
        district: { color: '#6f42c1', zIndex: 430, opacity: 0.6 },
        project:  { color: '#dc3545', zIndex: 440, opacity: 0.7 }
    };

    // Bounds for Egypt with a slight buffer to ensure borders are visible and usable.
    const EGYPT_BOUNDS = {
        southWest: [21.5, 24.0], // Slightly south of 22.0 and west of 25.0
        northEast: [32.5, 37.5]  // Slightly north of 31.8 and east of 37.0
    };

    // Cache to store promises of fetching polygons (key: apiPath)
    const _polygonCache = {};

    /**
     * Main Entry Point
     * @param {Object} options Configuration options
     */
    window.initLocationMap = function(options) {
        const config = normalizeOptions(options);

        // 1. Initialize Base Map
        const map = createMap(config);

        // 2. Setup Point/Marker Logic
        const markerTools = setupMarkerLogic(map, config);

        // 3. Setup Editable Polygon Logic (Drawing)
        // Passed markerTools to allow auto-centering pin on draw
        const drawTools = setupDrawLogic(map, config, markerTools);

        // 4. Load Context Layers (Background Polygons)
        // Store layer references to allow updates/replacement
        const contextLayers = {};
        if (config.useViewportLoading) {
            setupViewportLoading(map, config, contextLayers);
        } else {
            loadContextLayers(map, config, contextLayers);
        }

        // 5. Setup Search (Legacy support)
        setupSearchBox(map, markerTools, drawTools, config);

        // 6. External Hooks
        if (config.onMapInit) {
            config.onMapInit(map);
        }

        // Expose map globally for legacy scripts (e.g. window.map_map-1)
        window['map_' + config.elementId] = map;

        // Expose update function for external use (legacy)
        window.updateLocationMapBoundary = drawTools.updateBoundaryInput;
    };

    // --- Helper Functions ---

    /**
     * Normalizes and sets defaults for options
     */
    function normalizeOptions(options) {
        // Determine if valid coordinates were provided to decide whether to show a marker
        // Using loose equality check for null to catch undefined as well.
        // Sanitize: ensure dot decimal for robustness against locale issues
        let safeLat = null;
        let safeLng = null;
        if (options.lat != null && options.lng != null) {
            safeLat = parseFloat(String(options.lat).replace(',', '.'));
            safeLng = parseFloat(String(options.lng).replace(',', '.'));
        }

        const hasCoords = (safeLat != null && safeLng != null && !isNaN(safeLat) && !isNaN(safeLng));

        return {
            elementId: options.elementId,
            entityLevel: options.entityLevel,
            entityId: options.entityId ? String(options.entityId) : null,

            // DOM Selectors
            polygonFieldSelector: options.polygonFieldSelector,
            latFieldSelector: options.latFieldSelector,
            lngFieldSelector: options.lngFieldSelector,

            // Map State
            // If coords provided, use them. Else default to Cairo.
            lat: hasCoords ? safeLat : 30.0444,
            lng: hasCoords ? safeLng : 31.2357,
            hasInitialCoordinates: hasCoords,

            // Zoom: Use provided zoom, or 13 if coords exist, or 6 for country view
            zoom: options.zoom || (hasCoords ? 13 : 6),

            // Flags
            readOnly: !!options.readOnly,
            // Default to true (Lock to Egypt) unless explicitly false (Task 1)
            lockToEgypt: options.lockToEgypt !== undefined ? !!options.lockToEgypt : true,
            useViewportLoading: !!options.useViewportLoading,

            // Callbacks
            onPointChange: options.onPointChange,
            onPolygonChange: options.onPolygonChange,
            onMapInit: options.onMapInit
        };
    }

    /**
     * Creates and Configures the Leaflet Map
     */
    function createMap(config) {
        const map = L.map(config.elementId).setView([config.lat, config.lng], config.zoom);

        if (config.lockToEgypt) {
            const bounds = L.latLngBounds(EGYPT_BOUNDS.southWest, EGYPT_BOUNDS.northEast);
            map.setMaxBounds(bounds);
            map.options.maxBoundsViscosity = 1.0;
            map.options.minZoom = 5;
        }

        // Define Standard Layer (OpenStreetMap)
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        // Define Satellite Layer (Esri World Imagery)
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });

        // Add Default Layer
        osmLayer.addTo(map);

        // Add Layer Control
        const baseMaps = {
            "Map": osmLayer,
            "Satellite": satelliteLayer
        };
        L.control.layers(baseMaps).addTo(map);

        // Auto-switch to Satellite view when zooming in close (High detail / Low altitude)
        const SATELLITE_ZOOM_THRESHOLD = 15;
        let userManuallySwitchedLayer = false;

        // Detect manual layer changes
        map.on('baselayerchange', function(e) {
            // If the switch wasn't programmatic (we can't easily tell, so we assume user intent if it happens)
            // Ideally we'd set a flag when WE switch it, but Leaflet events trigger anyway.
            // A simple heuristic: if we are in the zone where we would have switched it anyway,
            // and the user switches it to something else, then disable auto-switch.
            // Actually, any manual interaction with the layer control should disable auto-pilot.
            // However, since we trigger this event programmatically too, we need to distinguish.

            // To reliably distinguish, we check if the event matches our expected state.
            const currentZoom = map.getZoom();
            const expectedLayer = (currentZoom >= SATELLITE_ZOOM_THRESHOLD) ? satelliteLayer : osmLayer;

            // If the new layer is NOT what our logic would put there, the user overrode it.
            if (e.layer !== expectedLayer) {
                userManuallySwitchedLayer = true;
            }
        });

        map.on('zoomend', function() {
            if (userManuallySwitchedLayer) return;

            const currentZoom = map.getZoom();

            // If zoomed in (>= threshold), switch to satellite if not already there
            if (currentZoom >= SATELLITE_ZOOM_THRESHOLD) {
                if (!map.hasLayer(satelliteLayer)) {
                    map.removeLayer(osmLayer);
                    map.addLayer(satelliteLayer);
                }
            }
            // If zoomed out (< threshold), switch back to standard map if not already there
            else {
                if (!map.hasLayer(osmLayer)) {
                    map.removeLayer(satelliteLayer);
                    map.addLayer(osmLayer);
                }
            }
        });

        return map;
    }

    /**
     * Handles Marker creation, movement, and input updates
     */
    function setupMarkerLogic(map, config) {
        let marker;

        // Initialize marker ONLY if coordinates were explicitly provided
        if (config.hasInitialCoordinates) {
             marker = L.marker([config.lat, config.lng], {draggable: !config.readOnly}).addTo(map);
        }

        function updateMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {draggable: !config.readOnly}).addTo(map);
                if (!config.readOnly) {
                    marker.on('dragend', function() {
                        const pos = marker.getLatLng();
                        updateInputs(pos.lat, pos.lng);
                    });
                }
            }
            updateInputs(lat, lng);
        }

        function updateInputs(lat, lng) {
            if (config.readOnly) return;

            const latVal = Number(lat).toFixed(7);
            const lngVal = Number(lng).toFixed(7);

            if (config.latFieldSelector) {
                const el = document.querySelector(config.latFieldSelector);
                if (el) el.value = latVal;
            }
            if (config.lngFieldSelector) {
                const el = document.querySelector(config.lngFieldSelector);
                if (el) el.value = lngVal;
            }

            // Legacy Display Elements
            const displayLat = document.getElementById('display-lat-' + config.elementId);
            const displayLng = document.getElementById('display-lng-' + config.elementId);
            if (displayLat) displayLat.innerText = latVal;
            if (displayLng) displayLng.innerText = lngVal;

            if (config.onPointChange) {
                config.onPointChange(lat, lng);
            }
        }

        if (!config.readOnly) {
            // Map click moves marker
            map.on('click', function(e) {
                updateMarker(e.latlng.lat, e.latlng.lng);
            });

            // Initial marker drag listener
            if (marker) {
                marker.on('dragend', function() {
                    const pos = marker.getLatLng();
                    updateInputs(pos.lat, pos.lng);
                });
            }
        }

        return {
            updateMarker,
            updateInputs
        };
    }

    /**
     * Loads background context layers (other regions, cities, etc.)
     * Optimized to reduce DOM nodes and refetches.
     */
    function loadContextLayers(map, config, contextLayers) {
        const apiPath = resolveApiPath(config.entityLevel);

        // Use cached promise if available to avoid duplicate requests
        if (!_polygonCache[apiPath]) {
            _polygonCache[apiPath] = fetch(apiPath).then(r => r.json());
        }

        _polygonCache[apiPath].then(data => {
            Object.keys(data).forEach(key => {
                const items = data[key];
                updateLayerForLevel(map, items, config, contextLayers);
            });
        }).catch(err => {
            console.error('Error fetching location polygons:', err);
        });
    }

    /**
     * Viewport Loading Logic
     */
    function setupViewportLoading(map, config, contextLayers) {
        let debounceTimer;
        const debouncedFetch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchPolygonsForViewport(map, config, contextLayers);
            }, 300);
        };

        map.on('moveend', debouncedFetch);
        map.on('zoomend', debouncedFetch);

        // Initial fetch
        debouncedFetch();
    }

    // Cache for viewport requests to prevent identical calls
    const _viewportCache = new Set();

    function fetchPolygonsForViewport(map, config, contextLayers) {
        const bounds = map.getBounds();
        const southWest = bounds.getSouthWest();
        const northEast = bounds.getNorthEast();

        // Round to 2 decimal places to create an approximate key
        const precision = 100;
        const keyParts = [
            Math.round(southWest.lat * precision),
            Math.round(southWest.lng * precision),
            Math.round(northEast.lat * precision),
            Math.round(northEast.lng * precision),
            config.entityLevel || 'all',
            map.getZoom()
        ];
        const cacheKey = keyParts.join('_');

        if (_viewportCache.has(cacheKey)) {
            return;
        }
        _viewportCache.add(cacheKey);

        // Construct API URL
        let apiPath = resolveApiPath(config.entityLevel);
        const separator = apiPath.includes('?') ? '&' : '?';
        apiPath += `${separator}min_lat=${southWest.lat}&min_lng=${southWest.lng}&max_lat=${northEast.lat}&max_lng=${northEast.lng}`;

        fetch(apiPath)
            .then(r => r.json())
            .then(data => {
                Object.keys(data).forEach(key => {
                    const items = data[key];
                    updateLayerForLevel(map, items, config, contextLayers);
                });
            })
            .catch(err => console.error('Error fetching viewport polygons:', err));
    }

    /**
     * Renders or updates a layer for a specific level.
     * Replaces existing layer to avoid duplication and flickering.
     */
    function updateLayerForLevel(map, items, config, contextLayers) {
        if (!items || items.length === 0) return;

        const level = items[0].level;
        const levelConf = LEVEL_CONFIG[level] || LEVEL_CONFIG.country;

        const layersForLevel = contextLayers[level] || {};

        // Separate polygon-based items and point-only items
        const polygonItems = items.filter(item => {
            if (!item.polygon) return false;
            if (config.entityLevel && config.entityId &&
                item.level === config.entityLevel && String(item.id) === config.entityId) {
                return false;
            }
            return true;
        });

        const pointItems = items.filter(item => {
            const hasPoint = item.lat != null && item.lng != null;
            if (!hasPoint) return false;
            if (config.entityLevel && config.entityId &&
                item.level === config.entityLevel && String(item.id) === config.entityId) {
                return false;
            }
            return !item.polygon; // prioritize polygon rendering when available
        });

        // Handle polygon layer
        if (polygonItems.length > 0) {
            // Ensure Pane exists
            const paneName = 'pane_' + level;
            if (!map.getPane(paneName)) {
                map.createPane(paneName);
                map.getPane(paneName).style.zIndex = levelConf.zIndex;
            }

            const geoJsonData = {
                type: "FeatureCollection",
                features: polygonItems.map(item => ({
                    type: "Feature",
                    geometry: item.polygon,
                    properties: {
                        name: item.name,
                        level: item.level,
                        id: item.id
                    }
                }))
            };

            const newPolygonLayer = L.geoJSON(geoJsonData, {
                pane: 'pane_' + level,
                style: {
                    color: levelConf.color,
                    fillColor: levelConf.color,
                    fillOpacity: levelConf.opacity,
                    weight: 2
                },
                onEachFeature: function(feature, layer) {
                    if (feature.properties && feature.properties.name) {
                        layer.bindTooltip(`${feature.properties.name} (${feature.properties.level})`, {
                            permanent: false,
                            direction: 'center'
                        });
                    }
                }
            });

            newPolygonLayer.addTo(map);

            if (layersForLevel.polygonLayer) {
                map.removeLayer(layersForLevel.polygonLayer);
            }

            layersForLevel.polygonLayer = newPolygonLayer;
        } else if (layersForLevel.polygonLayer) {
            map.removeLayer(layersForLevel.polygonLayer);
            layersForLevel.polygonLayer = null;
        }

        // Handle point-only layer (for locations without polygons)
        if (pointItems.length > 0) {
            const pointLayer = L.layerGroup(pointItems.map(item => {
                const marker = L.circleMarker([item.lat, item.lng], {
                    color: levelConf.color,
                    radius: 6,
                    weight: 2,
                    fillOpacity: 0.9
                });

                if (item.name) {
                    marker.bindTooltip(`${item.name} (${item.level})`, { permanent: false });
                }

                return marker;
            }));

            pointLayer.addTo(map);

            if (layersForLevel.pointLayer) {
                map.removeLayer(layersForLevel.pointLayer);
            }

            layersForLevel.pointLayer = pointLayer;
        } else if (layersForLevel.pointLayer) {
            map.removeLayer(layersForLevel.pointLayer);
            layersForLevel.pointLayer = null;
        }

        contextLayers[level] = layersForLevel;
    }

    /**
     * Resolves the API URL for polygons, handling localization prefixes.
     */
    function resolveApiPath(entityLevel) {
        let apiPath = '/admin/location-polygons';

        // Handle localized routes (e.g. /en/admin/...)
        const pathParts = window.location.pathname.split('/');
        // pathParts[0] is empty, [1] is first segment
        if (pathParts[1] && pathParts[1].length === 2 && pathParts[1] !== 'admin') {
             apiPath = '/' + pathParts[1] + apiPath;
        }

        const params = [];
        if (entityLevel) {
            params.push('level=' + encodeURIComponent(entityLevel));
        }

        if (params.length > 0) {
            apiPath += '?' + params.join('&');
        }

        return apiPath;
    }

    /**
     * Setup Drawing Tools for the main entity being edited
     */
    function setupDrawLogic(map, config, markerTools) {
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);
        const polygonInput = document.querySelector(config.polygonFieldSelector);

        // Load existing polygon
        if (polygonInput && polygonInput.value) {
            try {
                const val = polygonInput.value.trim();
                if (val && val !== 'null') {
                    const geoJsonData = JSON.parse(val);
                    L.geoJSON(geoJsonData, {
                        onEachFeature: function (feature, layer) {
                            drawnItems.addLayer(layer);
                        }
                    });
                    if (drawnItems.getBounds().isValid()) {
                        map.fitBounds(drawnItems.getBounds());
                    }
                }
            } catch (e) {
                console.error("Error parsing current entity boundary GeoJSON", e);
            }
        }

        // Internal update function
        function updateBoundaryInput() {
            const data = drawnItems.toGeoJSON();

            if (config.onPolygonChange) {
                config.onPolygonChange(data);
            }

            if (!polygonInput) return;

            if (data.features.length > 0) {
                if (config.entityLevel === 'project') {
                    // Projects expect a Feature
                    polygonInput.value = JSON.stringify(data.features[0]);
                } else {
                    // Locations expect Geometry
                    polygonInput.value = JSON.stringify(data.features[0].geometry);
                }
            } else {
                polygonInput.value = '';
            }
        }

        if (!config.readOnly) {
            // Task 3: Hide Drawing Tooltip Text programmatically
            if (L.drawLocal) {
                L.drawLocal.draw.handlers.polygon.tooltip.start = '';
                L.drawLocal.draw.handlers.polygon.tooltip.cont = '';
                L.drawLocal.draw.handlers.polygon.tooltip.end = '';
            }

            const drawControl = new L.Control.Draw({
                draw: {
                    polygon: { allowIntersection: false, showArea: true },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    marker: false,
                    circlemarker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });
            map.addControl(drawControl);

            map.on(L.Draw.Event.CREATED, function (e) {
                drawnItems.clearLayers();
                drawnItems.addLayer(e.layer);

                // Auto-center marker on the new shape (Task 4)
                if (markerTools && e.layer.getBounds) {
                    const center = e.layer.getBounds().getCenter();
                    markerTools.updateMarker(center.lat, center.lng);
                }

                updateBoundaryInput();
            });

            map.on(L.Draw.Event.EDITED, updateBoundaryInput);
            map.on(L.Draw.Event.DELETED, updateBoundaryInput);
        }

        return {
            drawnItems,
            updateBoundaryInput
        };
    }

    /**
     * Setup Nominatim Search (Legacy)
     */
    function setupSearchBox(map, markerTools, drawTools, config) {
        const searchBtn = document.getElementById('map-search-btn-' + config.elementId);
        if (!searchBtn) return;

        searchBtn.addEventListener('click', function() {
            const queryInput = document.getElementById('map-search-' + config.elementId);
            if (!queryInput) return;
            const query = queryInput.value;

            if (query) {
                fetch('https://nominatim.openstreetmap.org/search?format=json&polygon_geojson=1&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const result = data[0];
                            const lat = parseFloat(String(result.lat).replace(',', '.'));
                            const lon = parseFloat(String(result.lon).replace(',', '.'));

                            map.flyTo([lat, lon], 13);
                            markerTools.updateMarker(lat, lon);

                            if (!config.readOnly && result.geojson &&
                                (result.geojson.type === 'Polygon' || result.geojson.type === 'MultiPolygon')) {
                                if (confirm('Boundary found. Do you want to use it?')) {
                                    drawTools.drawnItems.clearLayers();
                                    const geoJsonLayer = L.geoJSON(result.geojson);
                                    geoJsonLayer.eachLayer(function(l) {
                                        drawTools.drawnItems.addLayer(l);
                                    });
                                    drawTools.updateBoundaryInput();
                                    map.fitBounds(drawTools.drawnItems.getBounds());
                                }
                            }
                        } else {
                            alert('Location not found');
                        }
                    })
                    .catch(err => console.error("Search error", err));
            }
        });
    }

})();
