@extends('admin.layouts.app')

@section('header', __('admin.developers'))

@section('content')
    <div class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <a href="{{ route($adminRoutePrefix.'developers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-fit">
            @lang('admin.create_new')
        </a>
        <form action="{{ route($adminRoutePrefix.'developers.index') }}" method="GET" class="flex flex-col md:flex-row gap-2">
            <select name="filter" class="border rounded px-4 py-2" onchange="this.form.submit()">
                <option value="active" {{ request('filter') === 'active' || !request('filter') ? 'selected' : '' }}>@lang('admin.activate')</option>
                <option value="inactive" {{ request('filter') === 'inactive' ? 'selected' : '' }}>@lang('admin.deactivate')</option>
                <option value="all" {{ request('filter') === 'all' ? 'selected' : '' }}>@lang('admin.all')</option>
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
        <form action="{{ route($adminRoutePrefix.'developers.bulk') }}" method="POST" id="bulk-form">
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
                                @lang('admin.name')
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
                                    {{ $developer->display_name }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    @php
                                        $rawLogo = $developer->logo_path ?? $developer->logo ?? null;
                                        $logoDebug = $developer->logo_debug ?? [];
                                    @endphp
                                    <div class="logo-thumb h-10 w-10 rounded border bg-white flex items-center justify-center overflow-hidden">
                                        @if($developer->logo_url)
                                            <img src="{{ $developer->logo_url }}" alt="{{ $developer->display_name }}" class="h-full w-full object-contain" onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden');">
                                            <span class="hidden text-[10px] text-gray-400">N/A</span>
                                        @else
                                            @if(config('app.debug'))
                                                <span class="text-[10px] text-red-500 text-center px-1 leading-tight">LOGO DEBUG: raw="{{ $rawLogo }}" {{ $logoDebug ? 'notes='.e(implode(' | ', $logoDebug)) : '' }} id={{ $developer->id }}</span>
                                            @else
                                                <span class="text-[10px] text-gray-400">N/A</span>
                                            @endif
                                        @endif
                                    </div>
                                    @if(config('app.debug'))
                                        <div class="mt-1 text-[10px] text-gray-500 leading-tight break-words">
                                            URL: {{ $developer->logo_url ?? 'null' }}
                                            @if(!empty($logoDebug))
                                                <br>notes={{ e(implode(' | ', $logoDebug)) }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <span class="relative inline-block px-3 py-1 font-semibold text-{{ $developer->is_active ? 'green' : 'red' }}-900 leading-tight">
                                        <span aria-hidden class="absolute inset-0 bg-{{ $developer->is_active ? 'green' : 'red' }}-200 opacity-50 rounded-full"></span>
                                        <span class="relative">{{ $developer->is_active ? __('admin.active') : __('admin.inactive') }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <a href="{{ route($adminRoutePrefix.'developers.edit', ['developer' => $developer->id]) }}" class="text-blue-600 hover:text-blue-900 mr-2">@lang('admin.edit')</a>
                                    <form action="{{ route($adminRoutePrefix.'developers.destroy', ['developer' => $developer->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('@lang('admin.confirm_delete')')">
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
