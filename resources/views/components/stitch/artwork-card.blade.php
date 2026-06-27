@props(['artwork', 'variant' => 'grid', 'stagger' => 0, 'offset' => 0])

@php
    $imageUrl = $artwork->imageUrl(\App\Enums\ArtworkImagePreset::forCardVariant($variant))
        ?? asset('images/stitch/spirit-of-twilight.jpg');
    $categoryLabel = $artwork->category?->name ?? 'Работа';
@endphp

@if ($variant === 'featured-large')
    <a href="{{ route('artworks.show', $artwork) }}" class="group stitch-reveal block md:col-span-8" style="transform: translateY({{ $offset }}px)">
        <div class="stitch-passe-partout mb-10">
            <div class="stitch-passe-partout-inner">
                <img
                    class="stitch-reveal-image aspect-[16/9] w-full object-cover transition-transform duration-[3000ms] group-hover:scale-105"
                    src="{{ $imageUrl }}"
                    alt="{{ $artwork->title }}"
                    loading="lazy"
                >
            </div>
        </div>
        <div class="flex items-baseline justify-between border-t border-outline/5 pt-8">
            <div>
                <h3 class="mb-2 font-headline text-[32px] text-on-surface">{{ $artwork->title }}</h3>
                @if ($artwork->description)
                    <p class="font-body text-on-surface-variant/60 italic">{{ Str::limit(strip_tags($artwork->description), 80) }}</p>
                @endif
            </div>
            @if ($artwork->formattedPrice())
                <span class="stitch-label text-on-surface-variant">{{ $artwork->formattedPrice() }}</span>
            @endif
        </div>
    </a>
@elseif ($variant === 'featured-side')
    <a href="{{ route('artworks.show', $artwork) }}" class="group stitch-reveal block md:col-span-4" style="transition-delay: {{ $stagger }}ms; transform: translateY({{ $offset }}px)">
        <div class="stitch-passe-partout mb-10">
            <div class="stitch-passe-partout-inner">
                <img
                    class="stitch-reveal-image aspect-[4/5] w-full object-cover transition-transform duration-[3000ms] group-hover:scale-105"
                    src="{{ $imageUrl }}"
                    alt="{{ $artwork->title }}"
                    loading="lazy"
                >
            </div>
        </div>
        <div class="border-t border-outline/5 pt-8">
            <h3 class="mb-2 font-headline text-[32px] text-on-surface">{{ $artwork->title }}</h3>
            <span class="stitch-label text-on-surface-variant">{{ $categoryLabel }}</span>
        </div>
    </a>
@elseif ($variant === 'featured-small')
    <a href="{{ route('artworks.show', $artwork) }}" class="group stitch-reveal block md:col-span-4" style="transition-delay: {{ $stagger }}ms; transform: translateY({{ $offset }}px)">
        <div class="stitch-passe-partout mb-8">
            <div class="stitch-passe-partout-inner">
                <img
                    class="stitch-reveal-image aspect-square w-full object-cover transition-transform duration-[3000ms] group-hover:scale-105"
                    src="{{ $imageUrl }}"
                    alt="{{ $artwork->title }}"
                    loading="lazy"
                >
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-outline/5 pt-6">
            <h3 class="font-headline text-[32px] text-on-surface">{{ $artwork->title }}</h3>
            @if ($artwork->formattedPrice())
                <span class="stitch-label text-on-surface-variant/60">{{ $artwork->formattedPrice() }}</span>
            @endif
        </div>
    </a>
@else
    <article class="stitch-gallery-item group stitch-tilt-container">
        <a href="{{ route('artworks.show', $artwork) }}" class="stitch-tilt-card relative block">
            <div class="stitch-glow-frame"></div>
            <div class="stitch-passe-partout-frame mb-8">
                <div class="stitch-fill-image">
                    <img
                        class="stitch-parallax-inner aspect-[3/4] w-full object-cover transition-transform duration-1000 group-hover:scale-105"
                        src="{{ $imageUrl }}"
                        alt="{{ $artwork->title }}"
                        loading="lazy"
                    >
                </div>
            </div>
            <div class="flex flex-col items-center space-y-3 text-center">
                <h3 class="font-headline text-[32px] text-on-surface">{{ $artwork->title }}</h3>
                <p class="stitch-label text-on-surface-variant">{{ $categoryLabel }}</p>
                @if ($artwork->formattedPrice())
                    <p class="font-body text-sm text-primary/80">{{ $artwork->formattedPrice() }}</p>
                @endif
            </div>
        </a>
    </article>
@endif
