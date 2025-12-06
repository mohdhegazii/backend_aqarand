@props([
    'title' => null,
    'description' => null,
])
<section {{ $attributes->merge(['class' => 'form-section']) }}>
    @if($title || $description)
        <div class="section-header">
            @if($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @if($description)
                <p class="card-subtitle">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="section-body">
        {{ $slot }}
    </div>
</section>
