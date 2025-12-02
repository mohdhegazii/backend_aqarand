@extends('admin.layouts.app')

@section('header')
    @lang('admin.edit_segment')
@endsection

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <form action="{{ route('admin.segments.update', $segment) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700">Name (EN)</label>
            <input type="text" name="name_en" value="{{ $segment->name_en }}" class="w-full border-gray-300 rounded mt-1" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Name (AR)</label>
            <input type="text" name="name_ar" value="{{ $segment->name_ar }}" class="w-full border-gray-300 rounded mt-1" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Slug</label>
            <input type="text" name="slug" value="{{ $segment->slug }}" class="w-full border-gray-300 rounded mt-1" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">@lang('admin.update')</button>
    </form>
</div>
@endsection
