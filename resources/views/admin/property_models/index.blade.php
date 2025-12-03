@extends('admin.layouts.app')

@section('header', 'Property Models')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Property Models</h3>
            <a href="{{ route('admin.property-models.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                @lang('admin.create_new')
            </a>
        </div>

        <div class="mb-4">
             <form method="GET" action="{{ route('admin.property-models.index') }}" class="flex gap-4">
                <select name="project_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">@lang('admin.all') @lang('admin.projects')</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name_en }}
                        </option>
                    @endforeach
                </select>
                <select name="unit_type_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">@lang('admin.all') @lang('admin.unit_types')</option>
                    @foreach($unitTypes as $type)
                        <option value="{{ $type->id }}" {{ request('unit_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name_en }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    @lang('admin.search')
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Specs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rtl:text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($propertyModels as $model)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $model->display_name }}</div>
                                <div class="text-sm text-gray-500">{{ $model->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $model->project->name_en ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $model->unitType->name_en ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $model->bedrooms }} BD | {{ $model->bathrooms }} BA<br>
                                {{ $model->min_bua }} - {{ $model->max_bua }} m²
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.property-models.edit', $model) }}" class="text-indigo-600 hover:text-indigo-900 mx-2">@lang('admin.edit')</a>
                                <form action="{{ route('admin.property-models.destroy', $model) }}" method="POST" class="inline" onsubmit="return confirm('@lang('admin.confirm_delete')')">
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
            {{ $propertyModels->links() }}
        </div>
    </div>
</div>
@endsection
