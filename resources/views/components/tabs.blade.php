@props([
    'tabs' => [],
    'active' => null,
])
<div {{ $attributes->merge(['class' => 'tabs']) }} role="tablist">
    @foreach($tabs as $key => $label)
        @php $isActive = ($active === null && $loop->first) || ($active !== null && $active == $key); @endphp
        <button type="button" role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}" class="tab-button {{ $isActive ? 'is-active' : '' }}">
            {{ $label }}
        </button>
    @endforeach
</div>
