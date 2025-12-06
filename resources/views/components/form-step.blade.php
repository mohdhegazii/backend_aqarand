@props([
    'title' => null,
    'number' => null,
])
<div {{ $attributes->merge(['class' => 'form-step']) }}>
    <div class="card-meta">
        <div class="badge">{{ $number ?? '•' }}</div>
        @if($title)
            <div class="card-title">{{ $title }}</div>
        @endif
    </div>
    <div class="card-content" style="margin-top: var(--spacing-3);">
        {{ $slot }}
    </div>
</div>
