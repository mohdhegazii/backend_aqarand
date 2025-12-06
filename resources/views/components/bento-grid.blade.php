@props([
    'columns' => 'repeat(auto-fit, minmax(260px, 1fr))',
])
<div {{ $attributes->merge(['class' => 'bento-grid']) }} style="grid-template-columns: {{ $columns }};">
    {{ $slot }}
</div>
