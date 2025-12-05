@extends('admin.layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('admin.media_details') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Preview -->
                        <div>
                            <div class="border rounded p-2 bg-gray-50 flex items-center justify-center">
                                <img src="{{ $mediaFile->url }}" alt="{{ $mediaFile->alt_en }}" class="max-w-full h-auto">
                            </div>
                            <div class="mt-4">
                                <h3 class="font-bold text-lg mb-2">{{ __('admin.file_info') }}</h3>
                                <ul class="list-disc list-inside text-sm text-gray-700">
                                    <li><strong>Filename:</strong> {{ $mediaFile->original_filename }}</li>
                                    <li><strong>Path:</strong> {{ $mediaFile->path }}</li>
                                    <li><strong>Size:</strong> {{ number_format($mediaFile->size_bytes / 1024, 2) }} KB</li>
                                    <li><strong>Dimensions:</strong> {{ $mediaFile->width }}x{{ $mediaFile->height }}</li>
                                    <li><strong>MIME:</strong> {{ $mediaFile->mime_type }}</li>
                                    <li><strong>Variant:</strong> {{ $mediaFile->variant_role }}</li>
                                    <li><strong>Created:</strong> {{ $mediaFile->created_at->format('Y-m-d H:i') }}</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Metadata Edit -->
                        <div>
                            <form action="{{ route('admin.media.update', $mediaFile) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Context</label>
                                    <input type="text" disabled value="{{ ucfirst($mediaFile->context_type) }} #{{ $mediaFile->context_id }}" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $mediaFile->country->name_en ?? '-' }} >
                                        {{ $mediaFile->region->name_en ?? '-' }} >
                                        {{ $mediaFile->city->name_en ?? '-' }} >
                                        {{ $mediaFile->district->name_en ?? '-' }}
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <label for="alt_en" class="block text-sm font-medium text-gray-700">ALT Text (English)</label>
                                    <textarea name="alt_en" id="alt_en" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('alt_en', $mediaFile->alt_en) }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="alt_ar" class="block text-sm font-medium text-gray-700">ALT Text (Arabic)</label>
                                    <textarea name="alt_ar" id="alt_ar" rows="3" dir="rtl" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('alt_ar', $mediaFile->alt_ar) }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-700 mb-2">SEO Keywords Snapshot</h4>
                                    <div class="bg-gray-100 p-3 rounded text-xs font-mono overflow-auto max-h-32">
                                        <strong>EN:</strong> {{ json_encode($mediaFile->seo_keywords_en) }}<br>
                                        <strong>AR:</strong> {{ json_encode($mediaFile->seo_keywords_ar) }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        {{ __('admin.save_changes') }}
                                    </button>

                                    <a href="{{ route('admin.media.index') }}" class="text-gray-600 hover:text-gray-900">
                                        {{ __('admin.back_to_list') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($mediaFile->variants->count() > 0)
                        <div class="mt-8 border-t pt-8">
                            <h3 class="font-bold text-lg mb-4">Variants</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($mediaFile->variants as $variant)
                                    <div class="border rounded p-2">
                                        <a href="{{ route('admin.media.show', $variant) }}">
                                            <img src="{{ $variant->url }}" class="h-24 object-cover mx-auto mb-2">
                                            <div class="text-center text-xs">
                                                <span class="font-bold">{{ $variant->variant_role }}</span><br>
                                                {{ $variant->extension }}
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
