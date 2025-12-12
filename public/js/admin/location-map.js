/**
 * Location Map Module
 *
 * Handles map initialization, polygon rendering, drawing tools, and location dropdown synchronization.
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

    const EGYPT_BOUNDS = { southWest: [21.5, 24.0], northEast: [32.5, 37.5] };
    const _polygonCache = {};

    function log(config, ...args) {
        if (config.debug) console.log('[LocationMap]', ...args);
    }

    /**
     * Main Entry Point
     */
    window.initLocationMap = function(options) {
        const config = normalizeOptions(options);
        log(config, 'Initializing map', config);

        const map = createMap(config);
        const markerTools = setupMarkerLogic(map, config);
        const drawTools = setupDrawLogic(map, config, markerTools);

        const contextLayers = {};
        if (config.useViewportLoading) {
            setupViewportLoading(map, config, contextLayers);
        } else {
            loadContextLayers(map, config, contextLayers);
        }

        setupSearchBox(map, markerTools, drawTools, config);

        if (config.onMapInit) config.onMapInit(map);

        window['map_' + config.elementId] = map;

        // --- Public API ---
        map.updateBoundaryInput = drawTools.updateBoundaryInput;

        map.setPolygon = function(geojson) {
            log(config, 'setPolygon called', geojson);
            drawTools.drawnItems.clearLayers();
            if (geojson) {
                try {
                    const layer = L.geoJSON(geojson);
                    layer.eachLayer(l => drawTools.drawnItems.addLayer(l));
                    if (drawTools.drawnItems.getBounds().isValid()) {
                        map.fitBounds(drawTools.drawnItems.getBounds());
                    }
                    drawTools.updateBoundaryInput();
                } catch (e) {
                    console.error('[LocationMap] Error setting polygon:', e);
                }
            } else {
                drawTools.updateBoundaryInput();
            }
        };

        map.showReferenceBoundary = function(geojson) {
            log(config, 'showReferenceBoundary called', geojson);
            if (map._referenceLayer) {
                map.removeLayer(map._referenceLayer);
                map._referenceLayer = null;
            }
            if (geojson) {
                try {
                    map._referenceLayer = L.geoJSON(geojson, {
                        style: {
                            color: '#3388ff',
                            weight: 2,
                            opacity: 0.6,
                            fillOpacity: 0.1,
                            dashArray: '5, 5'
                        },
                        interactive: false
                    }).addTo(map);
                    if (map._referenceLayer.getBounds().isValid()) {
                         map.fitBounds(map._referenceLayer.getBounds());
                    }
                } catch (e) {
                    console.error('[LocationMap] Error showing reference boundary:', e);
                }
            }
        };

        map.flyToLocation = function(lat, lng, zoom) {
             const safeLat = parseFloat(String(lat).replace(',', '.'));
             const safeLng = parseFloat(String(lng).replace(',', '.'));
             const safeZoom = zoom || 13;
             if (!isNaN(safeLat) && !isNaN(safeLng)) {
                 log(config, 'flyToLocation', safeLat, safeLng, safeZoom);
                 map.flyTo([safeLat, safeLng], safeZoom);
                 // Also move the marker if one exists
                 if(map.updateMarker) map.updateMarker(safeLat, safeLng);
             }
        };

        map.fetchAndShowBoundary = function(level, id) {
             if (!id) return;
             // Construct URL properly
             let url = resolveApiPath(config);
             // Ensure we append query params correctly
             const separator = url.includes('?') ? '&' : '?';
             url = `${url}${separator}level=${level}&id=${id}`;

             log(config, 'Fetching boundary', url);

             fetch(url)
                 .then(r => r.json())
                 .then(data => {
                     const key = level === 'region' ? 'regions' : (level === 'city' ? 'cities' : 'districts');
                     if (data[key] && data[key].length > 0) {
                         const item = data[key][0];
                         if (item.polygon) {
                             map.showReferenceBoundary(item.polygon);
                         } else if (item.lat != null && item.lng != null) {
                             let zoom = 10;
                             if(level === 'region') zoom = 9;
                             if(level === 'city') zoom = 11;
                             if(level === 'district') zoom = 13;
                             map.flyToLocation(item.lat, item.lng, zoom);
                         }
                     }
                 })
                 .catch(e => console.error('[LocationMap] Error fetching boundary', e));
        };

        /**
         * Centralized Dropdown Sync Logic
         * @param {Object} selectors { country: '#id'|Element, region: '#id'|Element, city: '#id'|Element, district: '#id'|Element }
         */
        map.setupLocationDropdowns = function(selectors) {
            log(config, 'setupLocationDropdowns', selectors);

            const getEl = (sel) => (typeof sel === 'string' ? document.querySelector(sel) : sel);

            const countryEl = getEl(selectors.country);
            const regionEl = getEl(selectors.region);
            const cityEl = getEl(selectors.city);
            const districtEl = getEl(selectors.district);

            function handleSelection(el, level) {
                if (!el) return;
                const id = el.value;
                if (!id) return;

                log(config, `Selection changed: ${level}=${id}`);

                // Priority 1: Check if option has data-lat/lng (fastest)
                const option = el.options[el.selectedIndex];
                const lat = option.getAttribute('data-lat');
                const lng = option.getAttribute('data-lng');

                // Fly to point first for immediate feedback
                if (lat && lng) {
                    let zoom = 6;
                    if(level === 'region') zoom = 9;
                    if(level === 'city') zoom = 11;
                    if(level === 'district') zoom = 13;
                    map.flyToLocation(lat, lng, zoom);
                }

                // Priority 2: Fetch boundary to show context
                // We only do this for region/city/district to show boundaries
                if (['region', 'city', 'district'].includes(level)) {
                    map.fetchAndShowBoundary(level, id);
                }
            }

            if(countryEl) countryEl.addEventListener('change', () => handleSelection(countryEl, 'country'));
            if(regionEl) regionEl.addEventListener('change', () => handleSelection(regionEl, 'region'));
            if(cityEl) cityEl.addEventListener('change', () => handleSelection(cityEl, 'city'));
            if(districtEl) districtEl.addEventListener('change', () => handleSelection(districtEl, 'district'));
        };

        window.updateLocationMapBoundary = drawTools.updateBoundaryInput;
        return map;
    };

    function normalizeOptions(options) {
        let safeLat = null, safeLng = null;
        if (options.lat != null && options.lng != null) {
            safeLat = parseFloat(String(options.lat).replace(',', '.'));
            safeLng = parseFloat(String(options.lng).replace(',', '.'));
        }
        const hasCoords = (safeLat != null && safeLng != null && !isNaN(safeLat) && !isNaN(safeLng));

        return {
            elementId: options.elementId,
            entityLevel: options.entityLevel,
            entityId: options.entityId ? String(options.entityId) : null,
            polygonFieldSelector: options.polygonFieldSelector,
            latFieldSelector: options.latFieldSelector,
            lngFieldSelector: options.lngFieldSelector,
            lat: hasCoords ? safeLat : 30.0444,
            lng: hasCoords ? safeLng : 31.2357,
            hasInitialCoordinates: hasCoords,
            zoom: options.zoom || (hasCoords ? 13 : 6),
            readOnly: !!options.readOnly,
            lockToEgypt: options.lockToEgypt !== undefined ? !!options.lockToEgypt : true,
            useViewportLoading: !!options.useViewportLoading,
            onPointChange: options.onPointChange,
            onPolygonChange: options.onPolygonChange,
            onMapInit: options.onMapInit,
            debug: !!options.debug,
            apiPolygonUrl: options.apiPolygonUrl
        };
    }

    function createMap(config) {
        const map = L.map(config.elementId).setView([config.lat, config.lng], config.zoom);
        if (config.lockToEgypt) {
            const bounds = L.latLngBounds(EGYPT_BOUNDS.southWest, EGYPT_BOUNDS.northEast);
            map.setMaxBounds(bounds);
            map.options.maxBoundsViscosity = 1.0;
            map.options.minZoom = 5;
        }
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' });
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri' });
        osmLayer.addTo(map);
        L.control.layers({ "Map": osmLayer, "Satellite": satelliteLayer }).addTo(map);

        const SATELLITE_ZOOM_THRESHOLD = 15;
        let userManuallySwitchedLayer = false;

        map.on('baselayerchange', (e) => {
            // If the user manually clicks the layer control, this flag becomes true
            // Logic: If the new layer is NOT what the auto-logic would have chosen, it's a manual override.
            const shouldBeSatellite = map.getZoom() >= SATELLITE_ZOOM_THRESHOLD;
            const isSatellite = e.layer === satelliteLayer;

            if (shouldBeSatellite !== isSatellite) {
                userManuallySwitchedLayer = true;
                log(config, 'User manually switched layer');
            }
        });

        map.on('zoomend', () => {
            if (userManuallySwitchedLayer) return;
            const currentZoom = map.getZoom();
            if (currentZoom >= SATELLITE_ZOOM_THRESHOLD) {
                if (!map.hasLayer(satelliteLayer)) {
                    log(config, 'Auto-switching to Satellite');
                    map.removeLayer(osmLayer);
                    map.addLayer(satelliteLayer);
                }
            } else {
                if (!map.hasLayer(osmLayer)) {
                    log(config, 'Auto-switching to OSM');
                    map.removeLayer(satelliteLayer);
                    map.addLayer(osmLayer);
                }
            }
        });
        return map;
    }

    function setupMarkerLogic(map, config) {
        let marker;
        if (config.hasInitialCoordinates) marker = L.marker([config.lat, config.lng], {draggable: !config.readOnly}).addTo(map);

        function updateMarker(lat, lng) {
            const safeLat = parseFloat(lat), safeLng = parseFloat(lng);
            if(isNaN(safeLat) || isNaN(safeLng)) return;

            if (marker) marker.setLatLng([safeLat, safeLng]);
            else {
                marker = L.marker([safeLat, safeLng], {draggable: !config.readOnly}).addTo(map);
                if (!config.readOnly) marker.on('dragend', () => { const pos = marker.getLatLng(); updateInputs(pos.lat, pos.lng); });
            }
            // If we are updating the marker programmatically (e.g. from dropdown), we should also update inputs
            updateInputs(safeLat, safeLng);
        }

        function updateInputs(lat, lng) {
            if (config.readOnly) return;
            const latVal = Number(lat).toFixed(7), lngVal = Number(lng).toFixed(7);
            if (config.latFieldSelector) { const el = document.querySelector(config.latFieldSelector); if(el) el.value = latVal; }
            if (config.lngFieldSelector) { const el = document.querySelector(config.lngFieldSelector); if(el) el.value = lngVal; }
            if (config.onPointChange) config.onPointChange(lat, lng);
        }

        if (!config.readOnly) {
            map.on('click', (e) => updateMarker(e.latlng.lat, e.latlng.lng));
            if (marker) marker.on('dragend', () => { const pos = marker.getLatLng(); updateInputs(pos.lat, pos.lng); });
        }
        map.updateMarker = updateMarker;
        return { updateMarker, updateInputs };
    }

    function resolveApiPath(config) {
        if (config.apiPolygonUrl) {
            return config.apiPolygonUrl;
        }

        // Fallback (try to construct from current location if not provided)
        let apiPath = '/admin/location-polygons';
        // Try to handle localized prefixes like /en/admin, /ar/admin
        // pathParts[1] is usually the locale
        const pathParts = window.location.pathname.split('/');
        if (pathParts[1] && pathParts[1].length === 2 && pathParts[1] !== 'admin') {
            apiPath = '/' + pathParts[1] + apiPath;
        }

        return apiPath;
    }

    function loadContextLayers(map, config, contextLayers) {
        let apiPath = resolveApiPath(config);
        if (config.entityLevel) apiPath += (apiPath.includes('?') ? '&' : '?') + `level=${encodeURIComponent(config.entityLevel)}`;

        log(config, 'Loading context layers', apiPath);
        if (!_polygonCache[apiPath]) _polygonCache[apiPath] = fetch(apiPath).then(r => { if(!r.ok) throw new Error('Network error'); return r.json(); });
        _polygonCache[apiPath].then(data => {
            Object.keys(data).forEach(key => updateLayerForLevel(map, data[key], config, contextLayers));
        }).catch(err => console.error('[LocationMap] Error fetching context:', err));
    }

    function setupViewportLoading(map, config, contextLayers) {
        let debounceTimer;
        const debouncedFetch = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchPolygonsForViewport(map, config, contextLayers), 300); };
        map.on('moveend', debouncedFetch);
        map.on('zoomend', debouncedFetch);
        debouncedFetch();
    }

    const _viewportCache = new Set();
    function fetchPolygonsForViewport(map, config, contextLayers) {
        const bounds = map.getBounds();
        const sw = bounds.getSouthWest(), ne = bounds.getNorthEast();
        const key = [Math.round(sw.lat*100), Math.round(sw.lng*100), Math.round(ne.lat*100), Math.round(ne.lng*100), config.entityLevel||'all', map.getZoom()].join('_');
        if (_viewportCache.has(key)) return;
        _viewportCache.add(key);

        let apiPath = resolveApiPath(config);
        apiPath += (apiPath.includes('?') ? '&' : '?') + `min_lat=${sw.lat}&min_lng=${sw.lng}&max_lat=${ne.lat}&max_lng=${ne.lng}`;
        fetch(apiPath).then(r => r.json()).then(data => {
            Object.keys(data).forEach(k => updateLayerForLevel(map, data[k], config, contextLayers));
        }).catch(err => console.error('[LocationMap] Error fetching viewport:', err));
    }

    function updateLayerForLevel(map, items, config, contextLayers) {
        if (!items || items.length === 0) return;
        const level = items[0].level;
        const conf = LEVEL_CONFIG[level] || LEVEL_CONFIG.country;
        const layers = contextLayers[level] || {};

        const polys = items.filter(i => i.polygon && !(config.entityLevel && config.entityId && i.level === config.entityLevel && String(i.id) === config.entityId));
        const points = items.filter(i => i.lat!=null && i.lng!=null && !i.polygon && !(config.entityLevel && config.entityId && i.level === config.entityLevel && String(i.id) === config.entityId));

        if (polys.length > 0) {
            const paneName = 'pane_' + level;
            if (!map.getPane(paneName)) { map.createPane(paneName); map.getPane(paneName).style.zIndex = conf.zIndex; }
            const gj = { type: "FeatureCollection", features: polys.map(i => ({ type: "Feature", geometry: i.polygon, properties: { name: i.name, level: i.level, id: i.id } })) };
            const ly = L.geoJSON(gj, { pane: paneName, style: { color: conf.color, fillColor: conf.color, fillOpacity: conf.opacity, weight: 2 }, onEachFeature: (f, l) => f.properties.name && l.bindTooltip(`${f.properties.name} (${f.properties.level})`, {permanent: false, direction: 'center'}) });
            ly.addTo(map);
            if (layers.poly) map.removeLayer(layers.poly);
            layers.poly = ly;
        } else if (layers.poly) { map.removeLayer(layers.poly); layers.poly = null; }

        if (points.length > 0) {
            const ly = L.layerGroup(points.map(i => {
                const m = L.circleMarker([i.lat, i.lng], { color: conf.color, radius: 6, weight: 2, fillOpacity: 0.9 });
                if(i.name) m.bindTooltip(`${i.name} (${i.level})`, {permanent: false});
                return m;
            }));
            ly.addTo(map);
            if (layers.point) map.removeLayer(layers.point);
            layers.point = ly;
        } else if (layers.point) { map.removeLayer(layers.point); layers.point = null; }

        contextLayers[level] = layers;
    }

    function setupDrawLogic(map, config, markerTools) {
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);
        const input = document.querySelector(config.polygonFieldSelector);

        if (input && input.value) {
            try {
                const val = input.value.trim();
                if (val && val !== 'null') {
                    L.geoJSON(JSON.parse(val), { onEachFeature: (f, l) => drawnItems.addLayer(l) });
                    if(drawnItems.getBounds().isValid()) map.fitBounds(drawnItems.getBounds());
                }
            } catch (e) { console.error("[LocationMap] Error parsing boundary", e); }
        }

        function updateBoundaryInput() {
            const data = drawnItems.toGeoJSON();
            if (config.onPolygonChange) config.onPolygonChange(data);
            if (!input) return;
            if (data.features.length > 0) {
                input.value = JSON.stringify(config.entityLevel === 'project' ? data.features[0] : data.features[0].geometry);
            } else input.value = '';
        }

        if (!config.readOnly) {
            if (L.drawLocal) { L.drawLocal.draw.handlers.polygon.tooltip.start = ''; L.drawLocal.draw.handlers.polygon.tooltip.cont = ''; L.drawLocal.draw.handlers.polygon.tooltip.end = ''; }
            const drawControl = new L.Control.Draw({ draw: { polygon: { allowIntersection: false, showArea: true }, polyline: false, rectangle: false, circle: false, marker: false, circlemarker: false }, edit: { featureGroup: drawnItems, remove: true } });
            map.addControl(drawControl);
            map.on(L.Draw.Event.CREATED, (e) => {
                drawnItems.clearLayers(); drawnItems.addLayer(e.layer);
                if (markerTools && e.layer.getBounds) { const c = e.layer.getBounds().getCenter(); markerTools.updateMarker(c.lat, c.lng); }
                updateBoundaryInput();
            });
            map.on(L.Draw.Event.EDITED, updateBoundaryInput);
            map.on(L.Draw.Event.DELETED, updateBoundaryInput);
        }
        map.drawnItems = drawnItems;
        return { drawnItems, updateBoundaryInput };
    }

    function setupSearchBox(map, markerTools, drawTools, config) {
        const btn = document.getElementById('map-search-btn-' + config.elementId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const inp = document.getElementById('map-search-' + config.elementId);
            if (!inp || !inp.value) return;
            log(config, 'Searching Nominatim', inp.value);
            fetch('https://nominatim.openstreetmap.org/search?format=json&polygon_geojson=1&q=' + encodeURIComponent(inp.value))
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const r = data[0];
                        const lat = parseFloat(String(r.lat).replace(',', '.')), lon = parseFloat(String(r.lon).replace(',', '.'));
                        map.flyTo([lat, lon], 13);
                        markerTools.updateMarker(lat, lon);
                        if (!config.readOnly && r.geojson && (r.geojson.type === 'Polygon' || r.geojson.type === 'MultiPolygon') && confirm('Boundary found. Use it?')) {
                            drawTools.drawnItems.clearLayers();
                            L.geoJSON(r.geojson).eachLayer(l => drawTools.drawnItems.addLayer(l));
                            drawTools.updateBoundaryInput();
                            map.fitBounds(drawTools.drawnItems.getBounds());
                        }
                    } else alert('Location not found');
                })
                .catch(e => console.error("Search error", e));
        });
    }

})();
