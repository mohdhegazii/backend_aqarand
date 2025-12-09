<div class="space-y-6">
    @foreach($amenitiesByCategory as $category)
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $category->display_name }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($category->amenities as $amenity)
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="amenity-{{ $amenity->id }}"
                                name="amenities[]"
                                type="checkbox"
                                value="{{ $amenity->id }}"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                {{ in_array($amenity->id, $selectedAmenityIds ?? []) ? 'checked' : '' }}
                            >
                        </div>
                        <div class="ltr:ml-3 rtl:mr-3 text-sm">
                            <label for="amenity-{{ $amenity->id }}" class="font-medium text-gray-700">
                                {{ $amenity->display_name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
