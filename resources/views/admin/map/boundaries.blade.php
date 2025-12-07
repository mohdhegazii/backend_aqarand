@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #global-map {
            height: calc(100vh - 200px);
            min-height: 500px;
            width: 100%;
            z-index: 1;
        }
        .legend {
            background: white;
            padding: 10px;
            line-height: 1.5;
            color: #555;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        .legend i {
            width: 18px;
            height: 18px;
            float: left;
            margin-right: 8px;
            opacity: 0.7;
        }
        .rtl .legend i {
            float: right;
            margin-left: 8px;
            margin-right: 0;
        }
    </style>
@endpush

@section('header', __('Boundaries Map'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div id="global-map" class="rounded-lg border border-gray-300"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('global-map').setView([26.8206, 30.8025], 6); // Egypt default

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Create custom panes for Z-ordering
            map.createPane('countryPane');
            map.getPane('countryPane').style.zIndex = 400;

            map.createPane('regionPane');
            map.getPane('regionPane').style.zIndex = 410;

            map.createPane('cityPane');
            map.getPane('cityPane').style.zIndex = 420;

            map.createPane('districtPane');
            map.getPane('districtPane').style.zIndex = 430;

            map.createPane('projectPane');
            map.getPane('projectPane').style.zIndex = 440;

            // Data from Controller
            var countries = @json($countries);
            var regions = @json($regions);
            var cities = @json($cities);
            var districts = @json($districts);
            var projects = @json($projects);

            // Styles
            const styles = {
                country: { color: '#00008B', fillColor: '#00008B', fillOpacity: 0.15, weight: 2 },
                region: { color: '#008080', fillColor: '#008080', fillOpacity: 0.2, weight: 2 },
                city: { color: '#FFA500', fillColor: '#FFA500', fillOpacity: 0.25, weight: 2 },
                district: { color: '#800080', fillColor: '#800080', fillOpacity: 0.3, weight: 2 },
                project: { color: '#FF0000', fillColor: '#FF0000', fillOpacity: 0.4, weight: 2 }
            };

            // Helper to add layers
            function addLayer(data, pane, style, popupField) {
                data.forEach(item => {
                    if (item.boundary_polygon) {
                        try {
                            var geoJson = typeof item.boundary_polygon === 'string'
                                ? JSON.parse(item.boundary_polygon)
                                : item.boundary_polygon;

                            L.geoJSON(geoJson, {
                                pane: pane,
                                style: style,
                                onEachFeature: function(feature, layer) {
                                    var name = item.name_local || item.name_en || item.name_ar;
                                    layer.bindPopup('<strong>' + name + '</strong>');
                                    layer.bindTooltip(name, { sticky: true });
                                }
                            }).addTo(map);
                        } catch (e) {
                            console.error('Error parsing GeoJSON for item', item.id, e);
                        }
                    }
                });
            }

            addLayer(countries, 'countryPane', styles.country, 'name_en');
            addLayer(regions, 'regionPane', styles.region, 'name_en');
            addLayer(cities, 'cityPane', styles.city, 'name_en');
            addLayer(districts, 'districtPane', styles.district, 'name_en');
            addLayer(projects, 'projectPane', styles.project, 'name_en');

            // Legend
            var legend = L.control({position: 'bottomright'});
            legend.onAdd = function (map) {
                var div = L.DomUtil.create('div', 'legend');
                var grades = ['Country', 'Region', 'City', 'District', 'Project'];
                var colors = [styles.country.color, styles.region.color, styles.city.color, styles.district.color, styles.project.color];

                for (var i = 0; i < grades.length; i++) {
                    div.innerHTML +=
                        '<i style="background:' + colors[i] + '"></i> ' +
                        grades[i] + '<br>';
                }
                return div;
            };
            legend.addTo(map);
        });
    </script>
@endpush
