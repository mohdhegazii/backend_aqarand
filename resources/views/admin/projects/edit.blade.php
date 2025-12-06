@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
@endpush

@section('header')
    {{ __('admin.edit_project') }}: {{ $project->name_ar ?? $project->name_en }}
@endsection

@section('content')
    @include('admin.projects.partials.form', ['project' => $project])
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
@endpush
