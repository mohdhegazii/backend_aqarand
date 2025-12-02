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

    <div class="mt-2 text-sm text-gray-500">
        @lang('admin.lat'): <span id="display-lat-{{ $mapId }}">{{ $lat ?? '-' }}</span>,
        @lang('admin.lng'): <span id="display-lng-{{ $mapId }}">{{ $lng ?? '-' }}</span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mapId = "{{ $mapId }}";
        var initialLat = {{ $lat ?? 30.0444 }}; // Default to Cairo
        var initialLng = {{ $lng ?? 31.2357 }};
        var zoomLevel = {{ $lat ? 13 : 6 }};

        var map = L.map(mapId).setView([initialLat, initialLng], zoomLevel);

        // Expose map to window for external control (e.g. flyTo)
        window['map_' + mapId] = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker;
        if ({{ $lat ? 'true' : 'false' }}) {
            marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);
        }

        function updateMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                marker.on('dragend', function(event) {
                    var position = marker.getLatLng();
                    updateInputs(position.lat, position.lng);
                });
            }
            updateInputs(lat, lng);
        }

        function updateInputs(lat, lng) {
            document.getElementById('lat-' + mapId).value = lat.toFixed(7);
            document.getElementById('lng-' + mapId).value = lng.toFixed(7);
            document.getElementById('display-lat-' + mapId).innerText = lat.toFixed(7);
            document.getElementById('display-lng-' + mapId).innerText = lng.toFixed(7);
        }

        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });

        // Search functionality (Mockup using Nominatim)
        document.getElementById('map-search-btn-' + mapId).addEventListener('click', function() {
            var query = document.getElementById('map-search-' + mapId).value;
            if (query) {
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            var lat = parseFloat(data[0].lat);
                            var lon = parseFloat(data[0].lon);
                            map.flyTo([lat, lon], 13);
                            updateMarker(lat, lon);
                        } else {
                            alert('Location not found');
                        }
                    });
            }
        });

        // FlyTo Logic if parent coordinates provided (passed via JS variable or data attribute if implemented)
        // For now, assuming standard init is enough.
    });
</script>
