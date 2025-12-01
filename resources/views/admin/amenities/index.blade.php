@extends('admin.layouts.app')

@section('header', __('admin.amenities'))

@section('content')
    <div class="mb-4 flex justify-between">
        <a href="{{ route('admin.amenities.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            @lang('admin.create')
        </a>
        <form action="{{ route('admin.amenities.index') }}" method="GET" class="flex">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="@lang('admin.search')..." class="border rounded-s px-4 py-2">
            <button type="submit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-e">
                @lang('admin.search')
            </button>
        </form>
    </div>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-start">@lang('admin.name_en')</th>
                    <th class="py-3 px-6 text-start">@lang('admin.name_local')</th>
                    <th class="py-3 px-6 text-start">@lang('admin.amenity_type')</th>
                    <th class="py-3 px-6 text-start">@lang('admin.icon_class')</th>
                    <th class="py-3 px-6 text-center">@lang('admin.status')</th>
                    <th class="py-3 px-6 text-center">@lang('admin.actions')</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @foreach($amenities as $amenity)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-start">
                            {{ $amenity->name_en }}
                        </td>
                        <td class="py-3 px-6 text-start">
                            {{ $amenity->name_local }}
                        </td>
                        <td class="py-3 px-6 text-start">
                            {{ ucfirst($amenity->amenity_type) }}
                        </td>
                        <td class="py-3 px-6 text-start">
                            {{ $amenity->icon_class }}
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($amenity->is_active)
                                <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">@lang('admin.active')</span>
                            @else
                                <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs">@lang('admin.inactive')</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2 rtl:space-x-reverse">
                                <a href="{{ route('admin.amenities.edit', $amenity) }}" class="text-purple-600 hover:text-purple-900">
                                    @lang('admin.edit')
                                </a>
                                <form action="{{ route('admin.amenities.destroy', $amenity) }}" method="POST" onsubmit="return confirm('@lang('admin.confirm_delete')')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        @lang('admin.delete')
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $amenities->links() }}
    </div>
@endsection
