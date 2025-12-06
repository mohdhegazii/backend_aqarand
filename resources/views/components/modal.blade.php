@props([
    'show' => false,
    'title' => null,
    'id' => null,
])
@if($show)
<div class="modal-layer" role="dialog" aria-modal="true" @if($id) id="{{ $id }}" @endif>
    <div class="modal-surface">
        @if($title)
            <div class="modal-header">
                <h3 class="card-title">{{ $title }}</h3>
                <span class="badge">@lang('admin.modal')</span>
            </div>
        @endif
        <div class="modal-body">
            {{ $slot }}
        </div>
    </div>
</div>
@endif
