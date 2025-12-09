@extends('admin.layouts.app')

@section('header', 'Units')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Units</h3>
            <a href="{{ route($adminRoutePrefix.'units.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                @lang('admin.create_new')
            </a>
        </div>

        <div class="mb-4">
             <form method="GET" action="{{ route($adminRoutePrefix.'units.index') }}">
                <div class="flex flex-col gap-4">
                    <div class="flex gap-4 items-end">
                        <div class="w-64">
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('admin.projects')</label>
                            <x-lookup.select
                                name="project_id"
                                url="/admin/lookups/projects"
                                placeholder="{{ __('admin.all') }} {{ __('admin.projects') }}"
                                :selected-id="request('project_id')"
                                :selected-text="request('project_id') && $projects->find(request('project_id')) ? $projects->find(request('project_id'))->name_en : ''"
                            />
                        </div>
                        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded h-[38px] mt-auto">
                            @lang('admin.search')
                        </button>
                        <a href="{{ route($adminRoutePrefix.'units.index') }}" class="text-gray-600 px-4 py-2 hover:underline mt-auto">Reset</a>
                    </div>

                     <!-- Amenity Filter -->
                    <div x-data="{ showAmenities: {{ request()->has('amenities') ? 'true' : 'false' }} }" class="border-t pt-4">
                        <button type="button" @click="showAmenities = !showAmenities" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <span x-text="showAmenities ? 'Hide Amenities Filter' : 'Show Amenities Filter ({{ $amenities->count() }})'"></span>
                            <i class="bi" :class="showAmenities ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>

                        <div x-show="showAmenities" class="mt-4">
                            @if($amenities->isNotEmpty())
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 max-h-60 overflow-y-auto p-2 border rounded-md bg-gray-50">
                                    @foreach($amenities as $amenity)
                                        <label class="inline-flex items-center hover:bg-gray-100 p-1 rounded cursor-pointer">
                                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                                {{ in_array($amenity->id, request('amenities', [])) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ltr:ml-2 rtl:mr-2 text-sm text-gray-700">{{ $amenity->display_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No amenities available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Unit # / Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($units as $unit)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $unit->display_name }}</div>
                                <div class="text-sm text-gray-500">{{ $unit->unit_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $unit->project->name_en ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $unit->unitType->name_en ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($unit->price) }} {{ $unit->currency_code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $unit->unit_status === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $unit->unit_status === 'sold' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $unit->unit_status === 'reserved' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    {{ ucfirst($unit->unit_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route($adminRoutePrefix.'units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-900 mx-2">@lang('admin.edit')</a>
                                <form action="{{ route($adminRoutePrefix.'units.destroy', $unit) }}" method="POST" class="inline" onsubmit="return confirm('@lang('admin.confirm_delete')')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">@lang('admin.delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $units->links() }}
        </div>
    </div>
</div>
@endsection
