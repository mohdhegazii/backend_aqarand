@extends('admin.layouts.app')

@section('header')
    @lang('admin.segments')
@endsection

@section('content')
<div class="mb-4 flex justify-between">
    <a href="{{ route('admin.segments.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        @lang('admin.create_new')
    </a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <form action="{{ route('admin.segments.bulk') }}" method="POST" id="bulk-form">
        @csrf
        <div class="p-4 border-b flex items-center space-x-2">
            <select name="action" class="border-gray-300 rounded text-sm">
                <option value="activate">@lang('admin.activate')</option>
                <option value="deactivate">@lang('admin.deactivate')</option>
            </select>
            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded text-sm">@lang('admin.apply')</button>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('admin.name')</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('admin.actions')</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($segments as $segment)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="ids[]" value="{{ $segment->id }}" class="row-checkbox">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $segment->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $segment->display_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.segments.edit', $segment) }}" class="text-indigo-600 hover:text-indigo-900">@lang('admin.edit')</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </form>
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
