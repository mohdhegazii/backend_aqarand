@extends('admin.layouts.app')

@section('header', __('admin.regions'))

@section('content')
    <!-- Index View similar to countries, but with bulk actions and map map picker logic is separate -->
    <!-- I will implement a simpler version for regions/cities/districts as they are repetitive -->
    <!-- But I need to implement them properly to match the requirement of "Checkboxes + bulk actions" and "Map picker" -->

    <div class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <a href="{{ route('admin.regions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-fit">
            @lang('admin.create_new')
        </a>
        <form action="{{ route('admin.regions.index') }}" method="GET" class="flex flex-col md:flex-row gap-2">
             <select name="filter" class="border rounded px-4 py-2" onchange="this.form.submit()">
                <option value="active" {{ request('filter') === 'active' || !request('filter') ? 'selected' : '' }}>@lang('admin.activate')</option>
                <option value="inactive" {{ request('filter') === 'inactive' ? 'selected' : '' }}>@lang('admin.deactivate')</option>
                <option value="all" {{ request('filter') === 'all' ? 'selected' : '' }}>All</option>
            </select>
            <div class="flex">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="@lang('admin.search')..." class="border rounded-s px-4 py-2">
                <button type="submit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-e">
                    @lang('admin.search')
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <form action="{{ route('admin.regions.bulk') }}" method="POST" id="bulk-form">
            @csrf
            <div class="p-4 border-b flex items-center space-x-2">
                <select name="action" class="border-gray-300 rounded text-sm">
                    <option value="activate">@lang('admin.activate')</option>
                    <option value="deactivate">@lang('admin.deactivate')</option>
                </select>
                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded text-sm">@lang('admin.apply')</button>
            </div>

            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-start">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th class="py-3 px-6 text-start">@lang('admin.name')</th>
                        <th class="py-3 px-6 text-start">@lang('admin.country')</th>
                        <th class="py-3 px-6 text-center">@lang('admin.actions')</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @foreach($regions as $region)
                        <tr class="border-b border-gray-200 hover:bg-gray-100 {{ !$region->is_active ? 'bg-gray-50 text-gray-400' : '' }}">
                            <td class="py-3 px-6 text-start">
                                <input type="checkbox" name="ids[]" value="{{ $region->id }}" class="row-checkbox">
                            </td>
                            <td class="py-3 px-6 text-start">
                                {{ $region->display_name }}
                            </td>
                            <td class="py-3 px-6 text-start">
                                {{ $region->country->display_name }}
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center space-x-2 rtl:space-x-reverse">
                                    <a href="{{ route('admin.regions.edit', $region) }}" class="text-purple-600 hover:text-purple-900">
                                        @lang('admin.edit')
                                    </a>
                                    <form action="{{ route('admin.regions.destroy', $region) }}" method="POST" onsubmit="return confirm('@lang('admin.confirm_delete')')" class="inline">
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
        </form>
    </div>

    <div class="mt-4">
        {{ $regions->links() }}
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.row-checkbox');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });
    </script>
@endsection
