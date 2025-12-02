@extends('admin.layouts.app')

@section('header', __('admin.developers'))

@section('content')
    @php($locale = app()->getLocale())
    <div class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <a href="{{ route('admin.developers.create', ['locale' => $locale]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-fit">
            @lang('admin.create_new')
        </a>
        <form action="{{ route('admin.developers.index', ['locale' => $locale]) }}" method="GET" class="flex flex-col md:flex-row gap-2">
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('admin.developers.bulk', ['locale' => $locale]) }}" method="POST" id="bulk-form">
            @csrf
            <div class="p-4 border-b flex items-center space-x-2">
                <select name="action" class="border-gray-300 rounded text-sm">
                    <option value="activate">@lang('admin.activate')</option>
                    <option value="deactivate">@lang('admin.deactivate')</option>
                </select>
                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded text-sm">@lang('admin.apply')</button>
            </div>

            <div class="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                                @lang('admin.name_en')
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                                @lang('admin.name_ar')
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                                @lang('admin.logo')
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                                @lang('admin.status')
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                                @lang('admin.actions')
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($developers as $developer)
                            <tr class="{{ !$developer->is_active ? 'bg-gray-50 text-gray-400' : '' }}">
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <input type="checkbox" name="ids[]" value="{{ $developer->id }}" class="row-checkbox">
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    {{ $developer->name_en ?? $developer->name }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    {{ $developer->name_ar }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    @if($developer->logo_path)
                                        <img src="{{ asset('storage/' . $developer->logo_path) }}" alt="Logo" class="h-8 object-contain">
                                    @endif
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <span class="relative inline-block px-3 py-1 font-semibold text-{{ $developer->is_active ? 'green' : 'red' }}-900 leading-tight">
                                        <span aria-hidden class="absolute inset-0 bg-{{ $developer->is_active ? 'green' : 'red' }}-200 opacity-50 rounded-full"></span>
                                        <span class="relative">{{ $developer->is_active ? __('admin.active') : __('admin.inactive') }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <a href="{{ route('admin.developers.edit', ['locale' => $locale, 'developer' => $developer->id]) }}" class="text-blue-600 hover:text-blue-900 mr-2">@lang('admin.edit')</a>
                                    <form action="{{ route('admin.developers.destroy', ['locale' => $locale, 'developer' => $developer->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('@lang('admin.confirm_delete')')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">@lang('admin.delete')</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $developers->withQueryString()->links() }}
                </div>
            </div>
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
