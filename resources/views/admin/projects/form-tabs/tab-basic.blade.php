<div class="space-y-6">
    <!-- Names -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Project Name (Arabic) <span class="text-red-500">*</span></label>
            <input type="text" name="name_ar" value="{{ old('name_ar', $project->name_ar ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required dir="rtl">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Project Name (English) <span class="text-red-500">*</span></label>
            <input type="text" name="name_en" value="{{ old('name_en', $project->name_en ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required dir="ltr">
        </div>
    </div>

    <!-- Project Area (Tagline Replacement) -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200" x-data="{ unit: '{{ old('project_area_unit', $project->project_area_unit ?? 'sqm') }}', value: {{ old('project_area_value', $project->project_area_value ?? 0) }} }">
        <h3 class="text-md font-medium text-gray-900 mb-3">Project Area</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Area Value</label>
                <input type="number" step="0.01" name="project_area_value" x-model.number="value"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Unit</label>
                <select name="project_area_unit" x-model="unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="sqm">Square Meters (sqm)</option>
                    <option value="feddan">Feddan</option>
                </select>
            </div>
            <div class="md:col-span-1 text-sm text-gray-500 pb-2">
                <span x-show="unit === 'feddan'">
                    ≈ <span x-text="(value * 4200).toLocaleString()"></span> sqm
                </span>
                <span x-show="unit === 'sqm'">
                    ≈ <span x-text="(value / 4200).toFixed(2)"></span> feddan
                </span>
                <p class="text-xs text-gray-400 mt-1">(1 Feddan = 4,200 sqm)</p>
            </div>
        </div>
    </div>

    <!-- Master Project -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200" x-data="{ isPartOfMaster: {{ old('is_part_of_master_project', $project->is_part_of_master_project ?? false) ? 'true' : 'false' }} }">
        <div class="flex items-center mb-3">
            <input type="checkbox" id="is_part_of_master_project" name="is_part_of_master_project" value="1" x-model="isPartOfMaster"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_part_of_master_project" class="ml-2 block text-sm font-medium text-gray-700">
                Is this project part of a larger Master Project?
            </label>
        </div>

        <div x-show="isPartOfMaster" class="mt-3">
            <label class="block text-sm font-medium text-gray-700">Select Master Project</label>
            <select name="master_project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Select Master Project --</option>
                @foreach($existingProjects as $p)
                    <option value="{{ $p->id }}" {{ (old('master_project_id', $project->master_project_id ?? '') == $p->id) ? 'selected' : '' }}>
                        {{ $p->name_en ?? $p->name_ar ?? $p->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">If the master project doesn't exist, create it first.</p>
        </div>
    </div>

    <!-- Dates & Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Sales Launch Date</label>
            <input type="date" name="sales_launch_date" value="{{ old('sales_launch_date', optional($project->sales_launch_date ?? null)->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Developer</label>
            <x-developers.select
                name="developer_id"
                :selected-id="old('developer_id', $project->developer_id ?? null)"
            />
        </div>
    </div>

    <!-- Flags -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
         <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Visibility & Status</label>
            <div class="flex items-center space-x-4 space-x-reverse">
                 <div class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $project->is_featured ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">Featured Project</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_top_project" value="1" {{ old('is_top_project', $project->is_top_project ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">Top Project</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="include_in_sitemap" value="1" {{ old('include_in_sitemap', $project->include_in_sitemap ?? true) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">Include in Sitemap</label>
                </div>
            </div>
         </div>

         <div>
             <label class="block text-sm font-medium text-gray-700 mb-2">Publish Status</label>
             <select name="publish_status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                 <option value="draft" {{ (old('publish_status', $project->publish_status ?? 'draft') == 'draft') ? 'selected' : '' }}>Draft</option>
                 <option value="published" {{ (old('publish_status', $project->publish_status ?? '') == 'published') ? 'selected' : '' }}>Published</option>
             </select>
         </div>
    </div>
</div>
