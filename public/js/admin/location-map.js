(function() {
    window.initLocationMap = function(options) {
        const {
            elementId,
            entityLevel,
            entityId,
            polygonFieldSelector,
            // Optional lat/lng for map center initialization
            latFieldSelector,
            lngFieldSelector,
            lat, lng, zoom,
            readOnly,
            lockToEgypt,
            onPointChange,
            onPolygonChange
        } = options;

        const mapId = elementId;
        const initialLat = lat || 30.0444; // Default to Cairo
        const initialLng = lng || 31.2357;
        const initialZoom = zoom || (lat ? 13 : 6);

        const map = L.map(mapId).setView([initialLat, initialLng], initialZoom);

        if (lockToEgypt) {
            const southWest = L.latLng(22.0, 24.0);
            const northEast = L.latLng(32.0, 37.0);
            const bounds = L.latLngBounds(southWest, northEast);
            map.setMaxBounds(bounds);
            map.options.maxBoundsViscosity = 1.0;
            map.options.minZoom = 5;
        }

        window['map_' + mapId] = map; // Expose globally if needed (backward compatibility)
        if (options.onMapInit) {
            options.onMapInit(map);
        }

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // --- Marker Handling (for point location) ---
        let marker;
        if (lat && lng) {
            marker = L.marker([lat, lng], {draggable: !readOnly}).addTo(map);
        }

        function updateMarker(newLat, newLng) {
            if (marker) {
                marker.setLatLng([newLat, newLng]);
            } else {
                marker = L.marker([newLat, newLng], {draggable: !readOnly}).addTo(map);
                if (!readOnly) {
                    marker.on('dragend', function(event) {
                        const position = marker.getLatLng();
                        updatePointInputs(position.lat, position.lng);
                    });
                }
            }
            updatePointInputs(newLat, newLng);
        }

        function updatePointInputs(newLat, newLng) {
            if (readOnly) return;
            if (latFieldSelector) {
                const latInput = document.querySelector(latFieldSelector);
                if (latInput) latInput.value = newLat.toFixed(7);
            }
            if (lngFieldSelector) {
                const lngInput = document.querySelector(lngFieldSelector);
                if (lngInput) lngInput.value = newLng.toFixed(7);
            }
            // Also update display if exists (legacy map_picker support)
            const displayLat = document.getElementById('display-lat-' + mapId);
            const displayLng = document.getElementById('display-lng-' + mapId);
            if (displayLat) displayLat.innerText = newLat.toFixed(7);
            if (displayLng) displayLng.innerText = newLng.toFixed(7);

            if (onPointChange) {
                onPointChange(newLat, newLng);
            }
        }

        if (!readOnly) {
            map.on('click', function(e) {
                // If draw control is active, don't move marker?
                // Leaflet Draw handles its own events. But simple click usually means "set marker here".
                // However, we need to be careful not to interfere with polygon drawing.
                // Usually L.Draw stops propagation.
                updateMarker(e.latlng.lat, e.latlng.lng);
            });

            if (marker) {
                 marker.on('dragend', function(event) {
                    const position = marker.getLatLng();
                    updatePointInputs(position.lat, position.lng);
                });
            }
        }

        // --- Polygons & Layers ---
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Colors and Z-Indices configuration
        const levelConfig = {
            country: { color: '#3388ff', zIndex: 100, opacity: 0.2 },
            region: { color: '#28a745', zIndex: 200, opacity: 0.4 },
            city: { color: '#fd7e14', zIndex: 300, opacity: 0.5 },
            district: { color: '#6f42c1', zIndex: 400, opacity: 0.6 },
            project: { color: '#dc3545', zIndex: 500, opacity: 0.7 }
        };

        // Fetch all polygons
        // We use a relative path assuming the script is running on the same domain
        // We need to handle localization prefix if present?
        // The API route is /admin/location-polygons, but if we are in /en/admin/...,
        // we might need to adjust. However, the JS runs in the browser context.
        // Let's try root relative '/admin/location-polygons' first.
        // If we are in localized route, it might be '/en/admin/location-polygons'.
        // Better to use a variable passed from backend, but if not available, try to detect or use absolute path if possible.
        // For now, let's assume '/admin/location-polygons' works or check window.location.pathname.

        let apiPath = '/admin/location-polygons';
        // Simple check if current path starts with /en/ or other locale
        const pathParts = window.location.pathname.split('/');
        if (pathParts[1] && pathParts[1].length === 2 && pathParts[1] !== 'admin') {
             // e.g. /en/admin/...
             apiPath = '/' + pathParts[1] + '/admin/location-polygons';
        } else if (pathParts[1] === 'admin') {
             apiPath = '/admin/location-polygons';
        }

        // Apply filters if specific level is requested
        const params = [];
        if (entityLevel) {
            params.push('level=' + encodeURIComponent(entityLevel));
            // Note: If entityLevel is 'project', controller will include projects automatically with level=project
        }

        if (params.length > 0) {
            apiPath += '?' + params.join('&');
        }

        fetch(apiPath)
            .then(response => response.json())
            .then(data => {
                // data = { countries: [], regions: [], ... }

                Object.keys(data).forEach(key => {
                    const items = data[key]; // array of objects {id, name, polygon, level}
                    if (!items) return;

                    items.forEach(item => {
                        // Skip if this is the entity currently being edited (we will load it separately into drawnItems)
                        if (entityLevel && entityId && item.level === entityLevel && String(item.id) === String(entityId)) {
                            return;
                        }

                        if (item.polygon) {
                            const config = levelConfig[item.level] || levelConfig.country;

                            try {
                                const layer = L.geoJSON(item.polygon, {
                                    style: function(feature) {
                                        return {
                                            color: config.color,
                                            fillColor: config.color,
                                            fillOpacity: config.opacity,
                                            weight: 2
                                        };
                                    },
                                    onEachFeature: function(feature, layer) {
                                        // Tooltip with name
                                        layer.bindTooltip(`${item.name} (${item.level})`, {
                                            permanent: false,
                                            direction: 'center'
                                        });
                                        // Bring to back/front based on z-index logic (Leaflet doesn't have z-index for vectors natively like markers)
                                        // But we can use map panes or just rely on order.
                                        // To strictly enforce order: countries first, then regions...
                                    }
                                });

                                // To manage z-order properly, we can use panes.
                                // Leaflet default panes: tilePane (0), overlayPane (400), shadowPane (500), markerPane (600), tooltipPane (650), popupPane (700).
                                // Vectors go to overlayPane (svg/canvas).
                                // We can create custom panes for each level if we really want strict z-ordering.

                                const paneName = 'pane_' + item.level;
                                if (!map.getPane(paneName)) {
                                    map.createPane(paneName);
                                    // Set zIndex for the pane
                                    map.getPane(paneName).style.zIndex = config.zIndex;
                                }

                                // Re-create layer with pane option
                                const paneLayer = L.geoJSON(item.polygon, {
                                    pane: paneName,
                                    style: function(feature) {
                                        return {
                                            color: config.color,
                                            fillColor: config.color,
                                            fillOpacity: config.opacity,
                                            weight: 2
                                        };
                                    },
                                    onEachFeature: function(feature, l) {
                                        l.bindTooltip(`${item.name} (${item.level})`, {
                                            permanent: false,
                                            direction: 'center'
                                        });
                                    }
                                });

                                paneLayer.addTo(map);

                            } catch (e) {
                                console.warn('Failed to render polygon for', item.name, e);
                            }
                        }
                    });
                });
            })
            .catch(err => console.error('Error fetching location polygons:', err));


        // --- Current Entity Polygon (Editable) ---
        const polygonInput = document.querySelector(polygonFieldSelector);

        if (polygonInput && polygonInput.value) {
            try {
                // Try parsing. Input value could be JSON string of a Geometry or Feature.
                // Or if empty string, nothing.
                const val = polygonInput.value;
                if (val && val.trim() !== '' && val !== 'null') {
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

        if (!readOnly) {
            // Setup Leaflet Draw for the current entity
            const drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true
                    },
                    polyline: false,
                    rectangle: false, // We only support polygons as per requirement (reusing existing logic)
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
                const layer = e.layer;
                drawnItems.clearLayers(); // Only allow one polygon for the current entity
                drawnItems.addLayer(layer);
                updateBoundaryInput();
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                updateBoundaryInput();
            });

            map.on(L.Draw.Event.DELETED, function (e) {
                updateBoundaryInput();
            });
        }

        function updateBoundaryInput() {
            const data = drawnItems.toGeoJSON();

            if (onPolygonChange) {
                onPolygonChange(data);
            }

            if (!polygonInput) return;

            // Current saving logic in map_picker uses data.features[0].geometry
            // Current saving logic in project form uses e.layer.toGeoJSON() which is a Feature.
            // We need to be consistent with what the backend expects.

            // For Projects: backend stores `map_polygon` as array/json.
            // For Locations: backend uses `ST_GeomFromGeoJSON(?)` which expects a geometry object (type: Polygon/MultiPolygon), NOT a Feature.

            if (data.features.length > 0) {
                if (entityLevel === 'project') {
                    // Projects expect the Feature object (or at least that's what was being saved: e.layer.toGeoJSON())
                    // Wait, `map_picker.blade.php` saves `data.features[0].geometry`.
                    // `projects/partials/form.blade.php` saves `e.layer.toGeoJSON()`.
                    // `e.layer.toGeoJSON()` returns a Feature.

                    // So we need to distinguish based on entityLevel.
                    polygonInput.value = JSON.stringify(data.features[0]); // Feature
                } else {
                    // Countries, Regions, Cities, Districts expect Geometry
                    polygonInput.value = JSON.stringify(data.features[0].geometry);
                }
            } else {
                polygonInput.value = '';
            }
        }

        // Expose update function if needed externally
        window.updateLocationMapBoundary = updateBoundaryInput;

        // Handle search button if present (Legacy map_picker support)
        const searchBtn = document.getElementById('map-search-btn-' + mapId);
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const queryInput = document.getElementById('map-search-' + mapId);
                if (!queryInput) return;
                const query = queryInput.value;

                if (query) {
                    fetch('https://nominatim.openstreetmap.org/search?format=json&polygon_geojson=1&q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                const result = data[0];
                                const lat = parseFloat(result.lat);
                                const lon = parseFloat(result.lon);

                                map.flyTo([lat, lon], 13);
                                updateMarker(lat, lon);

                                if (!readOnly && result.geojson && (result.geojson.type === 'Polygon' || result.geojson.type === 'MultiPolygon')) {
                                    if (confirm('Boundary found. Do you want to use it?')) {
                                        drawnItems.clearLayers();
                                        const geoJsonLayer = L.geoJSON(result.geojson);
                                        geoJsonLayer.eachLayer(function(l) {
                                            drawnItems.addLayer(l);
                                        });
                                        updateBoundaryInput();
                                        map.fitBounds(drawnItems.getBounds());
                                    }
                                }
                            } else {
                                alert('Location not found');
                            }
                        });
                }
            });
        }
    };
})();
