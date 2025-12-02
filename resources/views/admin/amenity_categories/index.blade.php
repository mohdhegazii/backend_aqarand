@extends('admin.layouts.app')

@section('header', __('admin.amenity_categories'))

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between mb-4">
                <div class="flex-1">
                     <form method="GET" action="{{ route('admin.amenity-categories.index') }}" class="flex">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="@lang('admin.search')" class="rounded-l border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-r">
                            @lang('admin.search')
                        </button>
                    </form>
                </div>
                <div>
                    <a href="{{ route('admin.amenity-categories.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        @lang('admin.create_new')
                    </a>
                </div>
            </div>

            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            @lang('admin.name_en')
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            @lang('admin.name_ar')
                        </th>
                         <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            @lang('admin.slug')
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rtl:text-right">
                            @lang('admin.sort_order')
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
                    @foreach($categories as $category)
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $category->name_en }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $category->name_ar }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $category->slug }}</p>
                            </td>
                             <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $category->sort_order }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="relative inline-block px-3 py-1 font-semibold text-{{ $category->is_active ? 'green' : 'red' }}-900 leading-tight">
                                    <span aria-hidden class="absolute inset-0 bg-{{ $category->is_active ? 'green' : 'red' }}-200 opacity-50 rounded-full"></span>
                                    <span class="relative">{{ $category->is_active ? __('admin.active') : __('admin.inactive') }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="{{ route('admin.amenity-categories.edit', $category->id) }}" class="text-blue-600 hover:text-blue-900 mr-4">@lang('admin.edit')</a>
                                <form action="{{ route('admin.amenity-categories.destroy', $category->id) }}" method="POST" class="inline-block" onsubmit="return confirm('@lang('admin.confirm_delete')')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">@lang('admin.delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
@endsection
