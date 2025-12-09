{{--
    Project Detail View (Amenities Section)
    Expected variables:
    - $project: Project model instance
    - $amenityDisplayGroups: Array from AmenityService::formatAmenitiesForDisplay()
    - $topAmenities: Array from AmenityService::getTopAmenitiesForProject() (Optional)
--}}

<div class="project-amenities-container">

    {{-- Top Amenities Highlight (Optional) --}}
    @if(isset($topAmenities) && count($topAmenities) > 0)
        <div class="top-amenities-highlight mb-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-lg font-bold mb-3">{{ __('Top Amenities') }}</h4>
            <div class="flex flex-wrap gap-4">
                @foreach($topAmenities as $amenity)
                    <div class="top-amenity-item flex items-center gap-2 bg-white px-3 py-2 rounded shadow-sm border">
                        @if(!empty($amenity['icon_class']))
                            <i class="{{ $amenity['icon_class'] }} text-primary"></i>
                        @elseif(!empty($amenity['image_url']))
                            <img src="{{ $amenity['image_url'] }}" alt="{{ $amenity['name'] }}" class="w-6 h-6 object-contain">
                        @endif
                        <span class="font-medium text-sm">{{ $amenity['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- All Amenities Grouped by Category --}}
    @if(isset($amenityDisplayGroups) && count($amenityDisplayGroups) > 0)
        <div class="amenities-grouped grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($amenityDisplayGroups as $group)
                <div class="amenity-group mb-4">
                    <h3 class="text-xl font-semibold mb-3 border-b pb-2 text-gray-800">{{ $group['category_name'] }}</h3>
                    <ul class="amenity-list space-y-2">
                        @foreach($group['items'] as $amenity)
                            <li class="amenity-item flex items-center gap-2">
                                @if(!empty($amenity['icon_class']))
                                    <i class="{{ $amenity['icon_class'] }} w-5 text-center text-gray-500"></i>
                                @elseif(!empty($amenity['image_url']))
                                    <img src="{{ $amenity['image_url'] }}" alt="{{ $amenity['name'] }}" class="w-5 h-5 object-contain">
                                @endif
                                <span class="text-gray-700">{{ $amenity['name'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

</div>
