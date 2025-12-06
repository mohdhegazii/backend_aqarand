@props([
    'title' => null,
    'subtitle' => null,
])
<header {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        @if($title)
            <div class="title">{{ $title }}</div>
        @endif
        @if($subtitle)
            <div class="subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    @isset($actions)
        <div class="actions">
            {{ $actions }}
        </div>
    @endisset
</header>
