@props([
    'lat' => null,
    'lng' => null,
    'zoom' => null,
    'polygon' => null,
    'readOnly' => false,
    'entityLevel' => null,
    'entityId' => null,
    'lockToEgypt' => true,
    'searchable' => true,
    'mapId' => 'map-' . uniqid(),
    'inputLatName' => 'lat',
    'inputLngName' => 'lng',
    'inputPolygonName' => 'boundary_geojson',
    'showDisplayValues' => true,
    'autoInit' => true,
])

<div class="mb-4 location-map-component">
    <label class="block font-medium text-sm text-gray-700 mb-2">@lang('admin.location_on_map')</label>

    @if($searchable && !$readOnly)
        <div class="flex space-x-2 mb-2 rtl:space-x-reverse">
            <input type="text" id="map-search-{{ $mapId }}" placeholder="@lang('admin.search_location')" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full p-2">
            <button type="button" id="map-search-btn-{{ $mapId }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                @lang('admin.search')
            </button>
        </div>
    @endif

    <div id="{{ $mapId }}" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;"></div>

    @if(!$readOnly)
        <input type="hidden" name="{{ $inputLatName }}" id="lat-{{ $mapId }}" value="{{ $lat ? number_format($lat, 7, '.', '') : '' }}">
        <input type="hidden" name="{{ $inputLngName }}" id="lng-{{ $mapId }}" value="{{ $lng ? number_format($lng, 7, '.', '') : '' }}">

        {{-- For Polygon, value must be encoded if it's an array/object --}}
        @php
            $polygonValue = is_array($polygon) || is_object($polygon) ? json_encode($polygon) : $polygon;
        @endphp
        <input type="hidden" name="{{ $inputPolygonName }}" id="boundary-{{ $mapId }}" value="{{ $polygonValue }}">
    @endif

    @if($showDisplayValues)
        <div class="mt-2 text-sm text-gray-500">
            @lang('admin.lat'): <span id="display-lat-{{ $mapId }}">{{ $lat ?? '-' }}</span>,
            @lang('admin.lng'): <span id="display-lng-{{ $mapId }}">{{ $lng ?? '-' }}</span>
        </div>
    @endif
</div>

@push('styles')
    @once
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
    @endonce
@endpush

@push('scripts')
    @once
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="/js/admin/location-map.js"></script>
    @endonce

    @if($autoInit)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initLocationMap({
                elementId: "{{ $mapId }}",
                entityLevel: "{{ $entityLevel }}",
                entityId: {{ $entityId ?? 'null' }},
                polygonFieldSelector: "#boundary-{{ $mapId }}",
                latFieldSelector: "#lat-{{ $mapId }}",
                lngFieldSelector: "#lng-{{ $mapId }}",
                lat: {{ number_format($lat ?? 30.0444, 7, '.', '') }},
                lng: {{ number_format($lng ?? 31.2357, 7, '.', '') }},
                zoom: {{ $zoom ?? ($lat ? 13 : 6) }},
                readOnly: {{ $readOnly ? 'true' : 'false' }},
                lockToEgypt: {{ $lockToEgypt ? 'true' : 'false' }},
            });
        });
    </script>
    @endif
@endpush
