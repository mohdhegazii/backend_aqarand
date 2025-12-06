@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $variantClass = [
        'primary' => 'ui-btn ui-btn-primary',
        'outline' => 'ui-btn ui-btn-outline',
        'ghost' => 'ui-btn ui-btn-ghost',
    ][$variant] ?? 'ui-btn ui-btn-primary';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $variantClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $variantClass]) }}>
        {{ $slot }}
    </button>
@endif
