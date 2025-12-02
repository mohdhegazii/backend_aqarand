@extends('admin.layouts.app')

@section('header')
    @lang('admin.create_category')
@endsection

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700">Segment</label>
            <select name="segment_id" class="w-full border-gray-300 rounded mt-1" required>
                <option value="">Select Segment</option>
                @foreach($segments as $segment)
                    <option value="{{ $segment->id }}">{{ $segment->name_en }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Name (EN)</label>
            <input type="text" name="name_en" class="w-full border-gray-300 rounded mt-1" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Name (AR)</label>
            <input type="text" name="name_ar" class="w-full border-gray-300 rounded mt-1" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Slug</label>
            <input type="text" name="slug" class="w-full border-gray-300 rounded mt-1" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Image</label>
            <input type="file" name="image" class="w-full mt-1">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">@lang('admin.save')</button>
    </form>
</div>
@endsection
