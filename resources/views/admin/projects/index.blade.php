@extends('admin.layouts.app')

@section('header')
    المشاريع
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-800">إدارة المشاريع</h3>
                <a href="{{ route($adminRoutePrefix.'projects.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <i class="bi bi-plus-lg"></i> إضافة مشروع جديد
                </a>
            </div>

            <!-- Search and Filter -->
            <form action="{{ route($adminRoutePrefix.'projects.index') }}" method="GET" class="mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">بحث</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث باسم المشروع..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="w-full md:w-1/3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطور</label>
                        {{-- Use ajax select as $developers list is limited --}}
                        <x-developers.select
                            name="developer_id"
                            :selected-id="request('developer_id')"
                            :placeholder="__('admin.project_wizard.select_developer')"
                        />
                    </div>
                    <div class="w-full md:w-auto pb-1">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                            بحث
                        </button>
                        <a href="{{ route($adminRoutePrefix.'projects.index') }}" class="text-gray-600 px-4 py-2 hover:underline">إعادة تعيين</a>
                    </div>
                </div>

                <!-- Amenity Filter -->
                <div x-data="{ showAmenities: {{ request()->has('amenities') ? 'true' : 'false' }} }" class="mt-4 border-t pt-4">
                    <button type="button" @click="showAmenities = !showAmenities" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                        <span x-text="showAmenities ? 'إخفاء فلتر المرافق' : 'عرض فلتر المرافق ({{ $amenities->count() }})'"></span>
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
                                        <span class="mr-2 text-sm text-gray-700">{{ $amenity->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">لا توجد مرافق متاحة.</p>
                        @endif
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                اسم المشروع
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                المطور
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الموقع
                            </th>
                            <!-- Status and Delivery Year Hidden as per requirements -->
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($projects as $project)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $project->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ app()->getLocale() === 'ar' ? ($project->name_en ?? '') : ($project->name_ar ?? '') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $project->developer?->display_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        {{ $project->city?->display_name ?? '-' }} -
                                        {{ $project->district?->display_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route($adminRoutePrefix.'projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900 ml-4">تعديل</a>

                                    <form action="{{ route($adminRoutePrefix.'projects.destroy', $project) }}" method="POST" class="inline-block" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    لا توجد مشاريع مضافة حتى الآن.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
@endsection
