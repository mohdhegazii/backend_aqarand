@extends('admin.layouts.app')

@section('header')
    <div class="flex justify-between items-center w-full">
        <span>@lang('admin.listings')</span>
        <a href="{{ route($adminRoutePrefix.'listings.create') }}" class="btn-primary flex items-center gap-2 text-sm">
            <i class="bi bi-plus-lg"></i>
            <span>@lang('admin.create_new')</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter/Search (Optional Placeholder) -->
    <!-- <div class="card flex gap-4">
        <input type="text" placeholder="Search listings..." class="form-input">
        <select class="form-select w-48"><option>All Statuses</option></select>
    </div> -->

    <!-- Desktop Table -->
    <div class="card overflow-hidden hidden md:block">
        <table class="w-full text-left rtl:text-right">
            <thead>
                <tr class="text-text-secondary text-xs uppercase border-b border-[var(--color-divider)]">
                    <th class="px-6 py-4 font-semibold">@lang('admin.title')</th>
                    <th class="px-6 py-4 font-semibold">@lang('admin.unit')</th>
                    <th class="px-6 py-4 font-semibold">@lang('admin.type')</th>
                    <th class="px-6 py-4 font-semibold">@lang('admin.status')</th>
                    <th class="px-6 py-4 font-semibold">@lang('admin.published_at')</th>
                    <th class="px-6 py-4 font-semibold text-right rtl:text-left">@lang('admin.actions')</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--color-divider)]">
                @forelse($listings as $listing)
                    <tr class="group hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-text-primary">{{ $listing->title_en }}</div>
                            <div class="text-xs text-text-secondary">{{ $listing->slug_en }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-text-primary font-medium">{{ $listing->unit->unit_number ?? 'N/A' }}</div>
                            <div class="text-xs text-text-secondary">{{ $listing->unit->project->name_en ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-text-primary capitalize">{{ $listing->listing_type }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $listing->status === 'published' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($listing->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary">
                            {{ $listing->published_at ? $listing->published_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right rtl:text-left">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route($adminRoutePrefix.'listings.edit', $listing) }}" class="p-2 rounded-[var(--radius-button)] text-primary-600 hover:bg-primary-50 transition-colors" title="@lang('admin.edit')">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route($adminRoutePrefix.'listings.destroy', $listing) }}" method="POST" class="inline" onsubmit="return confirm('@lang('admin.confirm_delete')')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-[var(--radius-button)] text-error-text hover:bg-error-bg transition-colors" title="@lang('admin.delete')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                            @lang('admin.no_records_found')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($listings->hasPages())
            <div class="px-6 py-4 border-t border-[var(--color-divider)]">
                {{ $listings->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Bento Grid (Cards) -->
    <div class="md:hidden grid grid-cols-1 gap-4">
        @foreach($listings as $listing)
            <div class="card">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="badge {{ $listing->status === 'published' ? 'badge-success' : 'badge-warning' }} mb-2">
                            {{ ucfirst($listing->status) }}
                        </span>
                        <h4 class="font-bold text-text-primary">{{ $listing->title_en }}</h4>
                        <p class="text-xs text-text-secondary">{{ $listing->unit->project->name_en ?? '-' }} • Unit {{ $listing->unit->unit_number ?? 'N/A' }}</p>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route($adminRoutePrefix.'listings.edit', $listing) }}" class="p-2 text-primary-600">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <!-- Delete Form Here if needed -->
                    </div>
                </div>
                <div class="flex justify-between items-center border-t border-[var(--color-divider)] pt-3 mt-2">
                    <span class="text-xs text-text-secondary capitalize">{{ $listing->listing_type }}</span>
                    <span class="text-xs text-text-secondary">{{ $listing->published_at ? $listing->published_at->format('M d, Y') : '-' }}</span>
                </div>
            </div>
        @endforeach
        <div class="mt-4">
            {{ $listings->links() }}
        </div>
    </div>
</div>
@endsection
