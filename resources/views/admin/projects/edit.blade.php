@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
@endpush

@section('header')
    {{ __('admin.edit_project') }}: {{ $project->name_ar ?? $project->name_en }}
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route($adminRoutePrefix.'projects.update', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('admin.developer') }}</label>
                        <x-developers.select
                            name="developer_id"
                            :selected-id="old('developer_id', $project->developer_id)"
                            :placeholder="__('admin.project_wizard.select_developer')"
                        />
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Master Project</label>
                         <x-lookup.select
                            name="master_project_id"
                            url="/admin/lookups/projects"
                            placeholder="{{ __('admin.select_project') }}"
                            :selected-id="old('master_project_id', $project->master_project_id)"
                            :selected-text="$project->masterProject ? ($project->masterProject->name_en ?? $project->masterProject->name_ar) : ''"
                        />
                    </div>
                </div>
                 {{-- Other fields would go here, but this is a placeholder view refactor --}}
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
@endpush
