@props([
    'lat' => 30.0444,
    'lng' => 31.2357,
    'zoom' => 6,
    'mapId' => 'map',
    'entityLevel' => null, // 'country', 'region', 'city', 'district', 'project'
    'entityId' => null,
    'polygon' => null,
    'readOnly' => false,
    'lockToEgypt' => true,
    'inputLatName' => 'lat',
    'inputLngName' => 'lng',
    'inputPolygonName' => 'polygon',
    'autoInit' => true,
    'searchable' => true,
    'useViewportLoading' => false,
    'onMapInit' => null, // Optional JS callback name
    'debug' => config('app.debug'),
    'apiPolygonUrl' => null // Optional override
])

@php
    // Determine the polygon API URL if not provided
    // We use the raw URL path because the JS append parameters manually.
    // Using route() ensures we respect the current domain/scheme.
    // Since we need to support both localized and non-localized, and the JS appends query params,
    // we should provide the base endpoint.
    // The route 'admin.location-polygons' is defined in admin.php
    $defaultPolygonUrl = route('admin.location-polygons');
    $finalPolygonUrl = $apiPolygonUrl ?? $defaultPolygonUrl;
@endphp

<div id="{{ $mapId }}" class="w-full h-96 rounded-lg border border-gray-300 shadow-sm z-0"></div>

{{-- Hidden Inputs for Form Submission --}}
@if(!$readOnly)
    <input type="hidden" name="{{ $inputLatName }}" id="lat-{{ $mapId }}" value="{{ $lat }}">
    <input type="hidden" name="{{ $inputLngName }}" id="lng-{{ $mapId }}" value="{{ $lng }}">
    <input type="hidden" name="{{ $inputPolygonName }}" id="boundary-{{ $mapId }}" value="{{ is_array($polygon) ? json_encode($polygon) : $polygon }}">
@endif

{{-- Legacy Search Input (Optional) --}}
@if($searchable && !$readOnly)
    <div class="mt-2 flex">
        <input type="text" id="map-search-{{ $mapId }}" class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('admin.search_location') }}">
        <button type="button" id="map-search-btn-{{ $mapId }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-indigo-600 hover:bg-indigo-700">
            {{ __('admin.search') }}
        </button>
    </div>
@endif

{{-- Push Scripts --}}
@once
    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

        <script src="{{ asset('js/admin/location-map.js') }}"></script>
    @endpush
@endonce

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if ({{ $autoInit ? 'true' : 'false' }}) {
             initLocationMap({
                 elementId: '{{ $mapId }}',
                 entityLevel: '{{ $entityLevel }}',
                 entityId: '{{ $entityId }}',
                 lat: {{ $lat ?? 30.0444 }},
                 lng: {{ $lng ?? 31.2357 }},
                 zoom: {{ $zoom ?? 6 }},
                 readOnly: {{ $readOnly ? 'true' : 'false' }},
                 lockToEgypt: {{ $lockToEgypt ? 'true' : 'false' }},
                 polygonFieldSelector: '#boundary-{{ $mapId }}',
                 latFieldSelector: '#lat-{{ $mapId }}',
                 lngFieldSelector: '#lng-{{ $mapId }}',
                 useViewportLoading: {{ $useViewportLoading ? 'true' : 'false' }},
                 onMapInit: {{ $onMapInit ?? 'null' }},
                 debug: {{ $debug ? 'true' : 'false' }},
                 apiPolygonUrl: '{{ $finalPolygonUrl }}'
             });
        }
    });
</script>
