@props(['artwork'])

<a href="{{ route('artworks.show', $artwork) }}" class="group block">
    <article class="overflow-hidden border border-white/10 bg-stone-900 transition duration-200 group-hover:border-amber-300/60 group-hover:bg-stone-900/70">
        <div class="aspect-[4/5] bg-stone-800">
            @if ($artwork->imageUrl())
                <img class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]" src="{{ $artwork->imageUrl(\App\Enums\ArtworkImagePreset::CardPortrait) }}" alt="{{ $artwork->title }}" loading="lazy">
            @else
                <div class="flex h-full items-center justify-center px-6 text-center text-sm text-stone-500">Фото будет добавлено</div>
            @endif
        </div>
        <div class="space-y-2 p-4">
            <h2 class="text-base font-medium leading-tight text-white">{{ $artwork->title }}</h2>
            @if ($artwork->formattedPrice())
                <p class="text-sm text-amber-200">{{ $artwork->formattedPrice() }}</p>
            @endif
        </div>
    </article>
</a>
