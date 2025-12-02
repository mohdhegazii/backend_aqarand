<div class="mb-4">
    <label class="block font-medium text-sm text-gray-700 mb-2">@lang('admin.location_on_map')</label>

    <div class="flex space-x-2 mb-2">
        <input type="text" id="map-search-{{ $mapId }}" placeholder="@lang('admin.search_location')" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full p-2">
        <button type="button" id="map-search-btn-{{ $mapId }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
            @lang('admin.search')
        </button>
    </div>

    <div id="{{ $mapId }}" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;"></div>

    <input type="hidden" name="lat" id="lat-{{ $mapId }}" value="{{ $lat }}">
    <input type="hidden" name="lng" id="lng-{{ $mapId }}" value="{{ $lng }}">
    <input type="hidden" name="boundary_geojson" id="boundary-{{ $mapId }}" value="{{ $boundary ?? '' }}">

    <div class="mt-2 text-sm text-gray-500">
        @lang('admin.lat'): <span id="display-lat-{{ $mapId }}">{{ $lat ?? '-' }}</span>,
        @lang('admin.lng'): <span id="display-lng-{{ $mapId }}">{{ $lng ?? '-' }}</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mapId = "{{ $mapId }}";
        var initialLat = {{ $lat ?? 30.0444 }}; // Default to Cairo
        var initialLng = {{ $lng ?? 31.2357 }};
        var zoomLevel = {{ $lat ? 13 : 6 }};
        var readOnly = {{ isset($readOnly) && $readOnly ? 'true' : 'false' }};

        var map = L.map(mapId).setView([initialLat, initialLng], zoomLevel);
        window['map_' + mapId] = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Marker Handling
        var marker;
        if ({{ $lat ? 'true' : 'false' }}) {
            marker = L.marker([initialLat, initialLng], {draggable: !readOnly}).addTo(map);
            if (!readOnly) {
                marker.on('dragend', function(event) {
                    var position = marker.getLatLng();
                    updateInputs(position.lat, position.lng);
                });
            }
        }

        function updateMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {draggable: !readOnly}).addTo(map);
                if (!readOnly) {
                    marker.on('dragend', function(event) {
                        var position = marker.getLatLng();
                        updateInputs(position.lat, position.lng);
                    });
                }
            }
            updateInputs(lat, lng);
        }

        function updateInputs(lat, lng) {
            if (readOnly) return;
            document.getElementById('lat-' + mapId).value = lat.toFixed(7);
            document.getElementById('lng-' + mapId).value = lng.toFixed(7);
            document.getElementById('display-lat-' + mapId).innerText = lat.toFixed(7);
            document.getElementById('display-lng-' + mapId).innerText = lng.toFixed(7);
        }

        if (!readOnly) {
            map.on('click', function(e) {
                updateMarker(e.latlng.lat, e.latlng.lng);
            });
        }

        // Polygon / Draw Handling
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Load existing boundary if any
        var existingBoundary = document.getElementById('boundary-' + mapId).value;
        if (existingBoundary) {
            try {
                // Check if it's already a GeoJSON string (from old input) or needs parsing
                // Usually comes as JSON string.
                // NOTE: ST_AsGeoJSON from MySQL returns a string representation of JSON.
                // If it's pure JSON object, L.geoJSON handles it.
                var geoJsonData = JSON.parse(existingBoundary);
                var geoJsonLayer = L.geoJSON(geoJsonData, {
                    onEachFeature: function (feature, layer) {
                        drawnItems.addLayer(layer);
                    }
                });
                if (drawnItems.getBounds().isValid()) {
                    map.fitBounds(drawnItems.getBounds());
                }
            } catch (e) {
                console.error("Error parsing boundary GeoJSON", e);
            }
        }

        if (!readOnly) {
            var drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true
                    },
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
                var layer = e.layer;
                drawnItems.clearLayers(); // Only allow one polygon
                drawnItems.addLayer(layer);
                updateBoundaryInput();
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                updateBoundaryInput();
            });

            map.on(L.Draw.Event.DELETED, function (e) {
                updateBoundaryInput();
            });
        } else {
             // If read only, just show the polygon (already added to drawnItems)
             // No controls.
        }

        function updateBoundaryInput() {
            var data = drawnItems.toGeoJSON();
            // We want the geometry of the first feature if available, or null
            if (data.features.length > 0) {
                document.getElementById('boundary-' + mapId).value = JSON.stringify(data.features[0].geometry);
            } else {
                document.getElementById('boundary-' + mapId).value = '';
            }
        }

        // Search functionality
        var searchBtn = document.getElementById('map-search-btn-' + mapId);
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                var query = document.getElementById('map-search-' + mapId).value;
                if (query) {
                    // Request polygon_geojson=1 to get boundaries
                    fetch('https://nominatim.openstreetmap.org/search?format=json&polygon_geojson=1&q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                var result = data[0];
                                var lat = parseFloat(result.lat);
                                var lon = parseFloat(result.lon);

                                map.flyTo([lat, lon], 13);
                                updateMarker(lat, lon);

                                // Auto-draw polygon if available and not read-only
                                if (!readOnly && result.geojson && (result.geojson.type === 'Polygon' || result.geojson.type === 'MultiPolygon')) {
                                    if (confirm('Boundary found. Do you want to use it?')) {
                                        drawnItems.clearLayers();
                                        var geoJsonLayer = L.geoJSON(result.geojson);
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
    });
</script>
