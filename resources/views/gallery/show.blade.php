<x-layouts.stitch :title="$artwork->title.' | Елена Буркальцева'" :active-nav="'gallery'" :categories="$categories">
    <section class="min-h-screen pt-32 pb-section-gap">
        <div class="mx-auto max-w-7xl px-margin-mobile md:px-margin-desktop">
            <div class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-center">
                <a href="{{ route('gallery.index') }}" class="group inline-flex items-center space-x-3 text-on-surface-variant/60 transition-colors duration-500 hover:text-primary">
                    <span class="material-symbols-outlined text-sm">arrow_back_ios</span>
                    <span class="stitch-label">Вернуться в галерею</span>
                </a>

                @if ($previousArtwork || $nextArtwork)
                    <div class="flex items-center space-x-4">
                        @if ($previousArtwork)
                            <a
                                href="{{ route('artworks.show', $previousArtwork) }}"
                                class="flex h-10 w-10 items-center justify-center border border-outline/10 text-on-surface-variant/60 transition-all hover:border-primary/40 hover:text-primary"
                                aria-label="Предыдущая работа"
                            >
                                <span class="material-symbols-outlined text-lg">chevron_left</span>
                            </a>
                        @endif
                        @if ($nextArtwork)
                            <a
                                href="{{ route('artworks.show', $nextArtwork) }}"
                                class="flex h-10 w-10 items-center justify-center border border-outline/10 text-on-surface-variant/60 transition-all hover:border-primary/40 hover:text-primary"
                                aria-label="Следующая работа"
                            >
                                <span class="material-symbols-outlined text-lg">chevron_right</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 items-start gap-20 lg:grid-cols-12">
                <div class="stitch-fade-in-up lg:col-span-7" style="animation-delay: 0.1s">
                    <div class="stitch-passe-partout-artwork group">
                        <div class="stitch-passe-partout-artwork-inner">
                            <div class="relative overflow-hidden">
                                @if ($artwork->imageUrl())
                                    <img class="h-auto w-full" src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}">
                                @else
                                    <img class="h-auto w-full" src="{{ asset('images/stitch/princess-never-smiled.jpg') }}" alt="{{ $artwork->title }}">
                                @endif
                                <div class="pointer-events-none absolute inset-0 bg-primary/5 opacity-0 transition-opacity duration-700 group-hover:opacity-100"></div>
                            </div>
                        </div>
                    </div>

                    @if ($relatedArtworks->isNotEmpty())
                        <div class="mt-12">
                            <div class="mb-4 flex items-center justify-between">
                                <span class="stitch-label text-primary/40">Другие работы</span>
                                <a href="{{ route('gallery.index') }}" class="text-[10px] tracking-[0.2em] text-on-surface-variant uppercase transition-colors hover:text-primary">Смотреть все</a>
                            </div>
                            <div class="stitch-thumbnail-strip flex gap-4 overflow-x-auto pb-4">
                                @foreach ($relatedArtworks as $related)
                                    <a
                                        href="{{ route('artworks.show', $related) }}"
                                        @class([
                                            'flex-shrink-0 w-20 aspect-square border p-1 transition-all',
                                            'border-primary/40 ring-1 ring-primary/20 bg-surface-container-low' => $related->is($artwork),
                                            'border-outline/10 bg-surface-container-low opacity-40 grayscale hover:grayscale-0 hover:opacity-100' => ! $related->is($artwork),
                                        ])
                                    >
                                        @if ($related->imageUrl())
                                            <img class="h-full w-full object-cover" src="{{ $related->imageUrl() }}" alt="{{ $related->title }}">
                                        @else
                                            <div class="h-full w-full bg-surface-container-high"></div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="stitch-fade-in-up flex flex-col space-y-12 lg:col-span-5" style="animation-delay: 0.3s">
                    <div>
                        <h1 class="mb-4 font-headline text-5xl leading-tight text-on-surface md:text-[64px]">{{ $artwork->title }}</h1>
                        @if ($artwork->category)
                            <p class="font-headline text-2xl tracking-wide text-primary/70 italic">{{ $artwork->category->name }}</p>
                        @endif
                    </div>

                    <div class="stitch-silver-line w-full"></div>

                    <div class="space-y-8">
                        @if ($artwork->description)
                            <div class="font-body text-lg leading-relaxed text-on-surface-variant">
                                {!! nl2br(e($artwork->description)) !!}
                            </div>
                        @else
                            <p class="font-body text-lg leading-relaxed text-on-surface-variant">
                                Глубокое исследование меланхолии «Серебряного века» — работа из личной коллекции художника.
                            </p>
                        @endif

                        <div class="border-l border-primary/20 py-2 pl-8">
                            <p class="font-body text-base leading-relaxed text-secondary/60 italic">
                                «Душа — это тихая галерея в лунном свете, где даже тени венчаны инеем».
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-12 pt-4">
                        @if ($artwork->category)
                            <div class="space-y-3">
                                <span class="block text-[10px] tracking-[0.3em] text-primary/40 uppercase">Категория</span>
                                <span class="font-body text-on-surface">{{ $artwork->category->name }}</span>
                            </div>
                        @endif
                        @if ($artwork->formattedPrice())
                            <div class="space-y-3">
                                <span class="block text-[10px] tracking-[0.3em] text-primary/40 uppercase">Стоимость</span>
                                <span class="font-body text-on-surface">{{ $artwork->formattedPrice() }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col space-y-6 pt-10">
                        <a href="{{ route('orders.create', $artwork) }}" class="stitch-btn-inquire text-center">
                            <span class="relative z-10">Узнать о наличии оригинала</span>
                        </a>
                    </div>

                    @if ($previousArtwork || $nextArtwork)
                        <div class="flex items-center justify-between border-t border-outline/10 pt-16">
                            @if ($previousArtwork)
                                <a href="{{ route('artworks.show', $previousArtwork) }}" class="group flex flex-col items-start gap-1">
                                    <span class="text-[10px] tracking-[0.2em] text-on-surface-variant/40 uppercase transition-colors group-hover:text-primary/40">Предыдущая</span>
                                    <span class="font-body text-on-surface transition-colors group-hover:text-primary">{{ $previousArtwork->title }}</span>
                                </a>
                            @else
                                <span></span>
                            @endif

                            @if ($nextArtwork)
                                <a href="{{ route('artworks.show', $nextArtwork) }}" class="group flex flex-col items-end gap-1 text-right">
                                    <span class="text-[10px] tracking-[0.2em] text-on-surface-variant/40 uppercase transition-colors group-hover:text-primary/40">Следующая</span>
                                    <span class="font-body text-on-surface transition-colors group-hover:text-primary">{{ $nextArtwork->title }}</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <x-stitch.footer />
</x-layouts.stitch>
