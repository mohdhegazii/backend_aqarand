<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('admin.media_manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.media.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('admin.search') }}</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('admin.context') }}</label>
                            <select name="context_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('admin.all') }}</option>
                                <option value="project" {{ request('context_type') == 'project' ? 'selected' : '' }}>Project</option>
                                <option value="unit" {{ request('context_type') == 'unit' ? 'selected' : '' }}>Unit</option>
                                <option value="listing" {{ request('context_type') == 'listing' ? 'selected' : '' }}>Listing</option>
                                <option value="blog" {{ request('context_type') == 'blog' ? 'selected' : '' }}>Blog</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('admin.variant_role') }}</label>
                            <select name="variant_role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('admin.all') }}</option>
                                <option value="original" {{ request('variant_role') == 'original' ? 'selected' : '' }}>Original</option>
                                <option value="webp" {{ request('variant_role') == 'webp' ? 'selected' : '' }}>WebP</option>
                                <option value="avif" {{ request('variant_role') == 'avif' ? 'selected' : '' }}>AVIF</option>
                                <option value="watermarked" {{ request('variant_role') == 'watermarked' ? 'selected' : '' }}>Watermarked</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                                {{ __('admin.filter') }}
                            </button>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('admin.preview') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('admin.details') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('admin.context') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('admin.seo_alt') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('admin.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($mediaFiles as $media)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex-shrink-0 h-20 w-20">
                                            <img class="h-20 w-20 rounded object-cover" src="{{ $media->url }}" alt="">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $media->original_filename }}</div>
                                        <div class="text-xs text-gray-500">{{ $media->variant_role }} | {{ $media->extension }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format($media->size_bytes / 1024, 2) }} KB</div>
                                        <div class="text-xs text-gray-500">{{ $media->width }}x{{ $media->height }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 capitalize">{{ $media->context_type }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $media->context_id }}</div>
                                        @if($media->project)
                                            <div class="text-xs text-blue-600">{{ $media->project->getDisplayNameAttribute() }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 w-48 truncate">{{ $media->alt_en }}</div>
                                        <div class="text-sm text-gray-500 w-48 truncate rtl:text-right" dir="rtl">{{ $media->alt_ar }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.media.show', $media) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('admin.view') }}</a>
                                        <form action="{{ route('admin.media.destroy', $media) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('admin.are_you_sure') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('admin.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        {{ __('admin.no_records_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $mediaFiles->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
