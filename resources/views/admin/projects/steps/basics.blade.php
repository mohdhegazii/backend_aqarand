@extends('admin.layouts.app')

@section('header')
    {{ $project->exists ? __('admin.edit_project') . ': ' . ($project->name_ar ?? $project->name_en) : __('admin.create_new') }}
@endsection

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <!-- Step Indicator -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">{{ __('admin.project_wizard.step_basics') }}</h2>
        <p class="text-sm text-gray-500">{{ __('admin.project_wizard.step_1_of_x') }}</p>
        <div class="mt-2 h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-indigo-600 rounded-full" style="width: 15%"></div>
        </div>
    </div>

    <form action="{{ route('admin.projects.steps.basics.store', $project->id) }}" method="POST">
        @csrf

        <div class="space-y-6">
            <!-- Project Name (Arabic) -->
            <div>
                <label for="name_ar" class="block text-sm font-semibold text-gray-700">
                    {{ __('admin.project_wizard.name_ar') }} <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name_ar"
                    name="name_ar"
                    value="{{ old('name_ar', $project->name_ar ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="{{ __('admin.project_wizard.name_ar_placeholder') }}"
                    required
                >
                @error('name_ar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Project Name (English) -->
            <div>
                <label for="name_en" class="block text-sm font-semibold text-gray-700">
                    {{ __('admin.project_wizard.name_en') }} <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name_en"
                    name="name_en"
                    value="{{ old('name_en', $project->name_en ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="{{ __('admin.project_wizard.name_en_placeholder') }}"
                    required
                >
                @error('name_en')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Developer -->
            <div>
                <label for="developer_id" class="block text-sm font-semibold text-gray-700">
                    {{ __('admin.project_wizard.developer') }} <span class="text-red-500">*</span>
                </label>
                <select
                    id="developer_id"
                    name="developer_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required
                >
                    <option value="">{{ __('admin.project_wizard.select_developer') }}</option>
                    @foreach($developers as $developer)
                        <option value="{{ $developer->id }}" {{ (old('developer_id', $project->developer_id) == $developer->id) ? 'selected' : '' }}>
                            {{ $developer->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('developer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-8 flex justify-between">
            <button
                type="button"
                class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 opacity-50 cursor-not-allowed"
                disabled
            >
                {{ __('admin.previous') }}
            </button>
            <button
                type="submit"
                class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                {{ __('admin.next') }}
            </button>
        </div>
    </form>
</div>
@endsection
