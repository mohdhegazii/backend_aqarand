<div class="bg-gray-50 p-4 rounded border border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Property Models</h3>
            <p class="text-sm text-gray-600">Manage the unit models that belong to this project.</p>
        </div>
        <a href="{{ route('admin.property-models.create', ['project_id' => $project->id, 'redirect' => route('admin.projects.edit', $project)]) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
            Add new property model
        </a>
    </div>

    @if($project->propertyModels->isEmpty())
        <div class="text-gray-600 text-sm">No property models have been added for this project yet.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bedrooms</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BUA</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($project->propertyModels as $model)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $model->name_en ?? $model->name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $model->unitType->name_en ?? $model->unitType->name ?? '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $model->bedrooms ?? '—' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                {{ $model->min_bua ? number_format($model->min_bua, 2) : '—' }}
                                —
                                {{ $model->max_bua ? number_format($model->max_bua, 2) : '—' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                {{ $model->min_price ? number_format($model->min_price, 0) : '—' }}
                                —
                                {{ $model->max_price ? number_format($model->max_price, 0) : '—' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.property-models.edit', [$model, 'project_id' => $project->id, 'redirect' => route('admin.projects.edit', $project)]) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                    <form action="{{ route('admin.property-models.destroy', $model) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this property model?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect" value="{{ route('admin.projects.edit', $project) }}">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
