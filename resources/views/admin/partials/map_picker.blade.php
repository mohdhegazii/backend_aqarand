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
<script src="/js/admin/location-map.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mapId = "{{ $mapId }}";
        var readOnly = {{ isset($readOnly) && $readOnly ? 'true' : 'false' }};
        var entityLevel = ''; // We need to determine entity level or pass it
        var entityId = null;

        // Try to infer entity level/ID from context or usage.
        // This partial is used in Country, Region, City, District views.
        // We can pass 'entityLevel' and 'entityId' to this partial.

        // However, existing usages don't pass these.
        // We can modify the usages or try to be smart.
        // It's safer to rely on passed variables, but we must handle fallback.

        entityLevel = "{{ $entityLevel ?? '' }}";
        entityId = {{ $entityId ?? 'null' }};

        // Fallback logic if variables not passed (though we should update views)
        if (!entityLevel) {
            // Check URL?
            if (window.location.href.includes('countries')) entityLevel = 'country';
            else if (window.location.href.includes('regions')) entityLevel = 'region';
            else if (window.location.href.includes('cities')) entityLevel = 'city';
            else if (window.location.href.includes('districts')) entityLevel = 'district';
        }

        initLocationMap({
            elementId: mapId,
            entityLevel: entityLevel,
            entityId: entityId,
            polygonFieldSelector: '#boundary-' + mapId,
            latFieldSelector: '#lat-' + mapId,
            lngFieldSelector: '#lng-' + mapId,
            lat: {{ $lat ?? 30.0444 }},
            lng: {{ $lng ?? 31.2357 }},
            readOnly: readOnly
        });
    });
</script>
