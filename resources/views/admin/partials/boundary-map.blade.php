@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        #{{ $mapId ?? 'boundary_map' }} {
            height: 400px;
            width: 100%;
            z-index: 1;
        }
    </style>
@endpush

@php
    $mapId = $mapId ?? 'boundary_map';
    $inputId = $fieldName ?? 'boundary_polygon';
    $currentPolygon = $existingPolygon ?? null;
    // Ensure existingPolygon is a JSON string if it's an array/object
    if (is_array($currentPolygon) || is_object($currentPolygon)) {
        $currentPolygon = json_encode($currentPolygon);
    }
@endphp

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Boundary Polygon') }}</label>
    <div id="{{ $mapId }}" class="rounded-lg border border-gray-300 dark:border-gray-600"></div>
    <input type="hidden" name="{{ $fieldName ?? 'boundary_polygon' }}" id="{{ $inputId }}" value="{{ $currentPolygon }}">
    <p class="text-xs text-gray-500 mt-1">{{ __('Use the map tools to draw a polygon boundary. Click the pentagon icon to start drawing.') }}</p>
</div>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('{{ $mapId }}').setView([26.8206, 30.8025], 6); // Default to Egypt view

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            var existingData = @json($existingPolygon ?? null);

            // If we have existing data, parse and display it
            if (existingData) {
                try {
                    var geoJsonLayer = L.geoJSON(existingData);
                    geoJsonLayer.eachLayer(function (layer) {
                        drawnItems.addLayer(layer);
                    });
                    if (drawnItems.getBounds().isValid()) {
                        map.fitBounds(drawnItems.getBounds());
                    }
                } catch (e) {
                    console.error('Invalid GeoJSON', e);
                }
            }

            var drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true
                    },
                    marker: false,
                    polyline: false,
                    circle: false,
                    circlemarker: false,
                    rectangle: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });
            map.addControl(drawControl);

            function updateInput() {
                var data = drawnItems.toGeoJSON();
                // We only want the geometry of the first feature if it exists, or null
                // But Leaflet Draw creates a FeatureCollection.
                // If we want to store just the geometry or the feature collection?
                // The prompt implies storing "the polygon as GeoJSON".
                // Usually one polygon per entity.

                if (data.features.length > 0) {
                     // For simplicity, we can store the whole FeatureCollection or just the first geometry.
                     // Let's store the first geometry to keep it simple, or the FeatureCollection.
                     // Storing the FeatureCollection is safer if they draw multiple polygons (multipolygon).
                     document.getElementById('{{ $inputId }}').value = JSON.stringify(data);
                } else {
                    document.getElementById('{{ $inputId }}').value = '';
                }
            }

            map.on(L.Draw.Event.CREATED, function (e) {
                // Clear existing layers to ensure only one polygon (if we want strictly one)
                // or allow multiple? "Store polygon boundaries" (plural/singular ambiguous).
                // Usually a region is one polygon, but could be multipolygon.
                // Let's clear previous to keep it simple for now, assuming 1 main polygon.
                drawnItems.clearLayers();

                var layer = e.layer;
                drawnItems.addLayer(layer);
                updateInput();
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                updateInput();
            });

            map.on(L.Draw.Event.DELETED, function (e) {
                updateInput();
            });

            // Fix map sizing if inside a hidden tab or modal
            setTimeout(function(){ map.invalidateSize(); }, 500);

            // If inside a tab, we might need a trigger.
            // Assuming standard load for now.
        });
    </script>
@endpush
