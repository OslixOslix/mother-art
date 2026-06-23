@props(['src', 'zoomSrc' => null, 'alt', 'imgClass' => ''])

@php
    $zoomSrc = $zoomSrc ?? $src;
@endphp

<div class="stitch-artwork-zoom" data-artwork-zoom>
    <img
        {{ $attributes->merge(['class' => 'stitch-artwork-zoom-source '.$imgClass, 'src' => $src, 'alt' => $alt, 'loading' => 'lazy']) }}
    >
    <div class="stitch-artwork-zoom-indicator" hidden></div>
    <div class="stitch-artwork-zoom-panel" aria-hidden="true">
        <div class="stitch-artwork-zoom-viewport">
            <img class="stitch-artwork-zoom-full" data-zoom-src="{{ $zoomSrc }}" src="" alt="">
        </div>
    </div>
</div>
