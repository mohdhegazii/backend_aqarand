@props([
    'variant' => null,
])
@php
    $class = 'badge';
    if($variant) {
        $class .= ' is-' . $variant;
    }
@endphp
<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</span>
