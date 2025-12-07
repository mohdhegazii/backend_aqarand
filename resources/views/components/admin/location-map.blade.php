@props([
    'mapId' => 'location-map',
    'lat' => 30.0444,
    'lng' => 31.2357,
    'zoom' => 10,
    'entityLevel' => null,
    'entityId' => null,
    'polygonFieldSelector' => '#polygon',
    'latFieldSelector' => '#lat',
    'lngFieldSelector' => '#lng',
    'readOnly' => false
])

<div id="{{ $mapId }}" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;"></div>

<script src="/js/admin/location-map.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initLocationMap({
            elementId: '{{ $mapId }}',
            entityLevel: '{{ $entityLevel }}',
            entityId: {{ $entityId ?? 'null' }},
            polygonFieldSelector: '{{ $polygonFieldSelector }}',
            latFieldSelector: '{{ $latFieldSelector }}',
            lngFieldSelector: '{{ $lngFieldSelector }}',
            lat: {{ $lat }},
            lng: {{ $lng }},
            zoom: {{ $zoom }},
            readOnly: {{ $readOnly ? 'true' : 'false' }}
        });
    });
</script>
