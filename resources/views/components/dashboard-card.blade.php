@props([
    'title' => null,
    'subtitle' => null,
    'meta' => null,
])
<div {{ $attributes->merge(['class' => 'dashboard-card']) }}>
    @if($title || $subtitle || $meta)
        <div class="card-meta">
            <div>
                @if($title)
                    <div class="card-title">{{ $title }}</div>
                @endif
                @if($subtitle)
                    <div class="card-subtitle">{{ $subtitle }}</div>
                @endif
            </div>
            @if($meta)
                <div class="badge is-primary">{{ $meta }}</div>
            @endif
        </div>
    @endif
    <div class="card-content">
        {{ $slot }}
    </div>
</div>
