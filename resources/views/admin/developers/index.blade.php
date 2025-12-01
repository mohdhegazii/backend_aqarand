@extends('admin.layouts.app')

@section('header', __('admin.developers'))

@section('content')
    <div class="mb-4 flex justify-between items-center">
        <form action="{{ route('admin.developers.index') }}" method="GET" class="flex">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="@lang('admin.search')..." class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                @lang('admin.search')
            </button>
        </form>
        <a href="{{ route('admin.developers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            @lang('admin.create') @lang('admin.developers')
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            ID
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            @lang('admin.name')
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            @lang('admin.slug')
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
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ $developer->id }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ $developer->name }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ $developer->slug }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="relative inline-block px-3 py-1 font-semibold text-{{ $developer->is_active ? 'green' : 'red' }}-900 leading-tight">
                                    <span aria-hidden class="absolute inset-0 bg-{{ $developer->is_active ? 'green' : 'red' }}-200 opacity-50 rounded-full"></span>
                                    <span class="relative">{{ $developer->is_active ? __('admin.active') : __('admin.inactive') }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="{{ route('admin.developers.edit', $developer->id) }}" class="text-blue-600 hover:text-blue-900 mr-2">@lang('admin.edit')</a>
                                <form action="{{ route('admin.developers.destroy', $developer->id) }}" method="POST" class="inline-block" onsubmit="return confirm('@lang('admin.confirm_delete')')">
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
    </div>
@endsection
