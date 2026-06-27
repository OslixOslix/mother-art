<x-layouts.stitch
    :title="$activeCategory ? $activeCategory->name.' | Галерея' : 'Душа — безмолвная галерея'"
    :active-nav="'gallery'"
>
    <header class="mx-auto max-w-5xl px-margin-mobile pt-60 pb-32 text-center">
        <div class="mb-8 flex items-center justify-center space-x-4 opacity-0 transition-opacity duration-1000" id="gallery-hero-ornament">
            <div class="stitch-ornament-line w-16"></div>
            <span class="material-symbols-outlined text-xl text-primary">blur_on</span>
            <div class="stitch-ornament-line w-16"></div>
        </div>
        <h1
            class="mb-8 translate-y-10 font-headline text-5xl text-on-surface opacity-0 transition-all duration-1000 ease-out md:text-[64px]"
            id="gallery-hero-title"
        >
            {{ $activeCategory?->name ?? 'Душа — безмолвная галерея' }}
        </h1>
        <p
            class="mx-auto max-w-2xl font-body text-xl font-extralight text-on-surface-variant/70 italic opacity-0 transition-all duration-1000 delay-300"
            id="gallery-hero-subtitle"
        >
            @if ($activeCategory)
                Работы из раздела «{{ $activeCategory->name }}».
            @else
                Исследование меланхоличной красоты Серебряного века через символы, туман и лунный свет.
            @endif
        </p>

        @if ($categories->isNotEmpty())
            <nav class="mt-12 flex flex-wrap items-center justify-center gap-4">
                <a
                    href="{{ route('gallery.index') }}"
                    @class([
                        'stitch-nav-link px-4 py-2',
                        'is-active is-active--underlined' => ! $activeCategory,
                    ])
                >Все</a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('gallery.category', $category) }}"
                        @class([
                            'stitch-nav-link px-4 py-2',
                            'is-active is-active--underlined' => $activeCategory?->is($category),
                        ])
                    >{{ $category->name }}</a>
                @endforeach
            </nav>
        @endif
    </header>

    <section class="mx-auto max-w-7xl px-margin-mobile pb-section-gap md:px-margin-desktop">
        @if ($artworks->count())
            <div
                class="stitch-gallery-grid grid grid-cols-1 items-start gap-x-item-gap gap-y-32 md:grid-cols-2 lg:grid-cols-3"
                data-infinite-scroll-url="{{ route('gallery.load-more', $activeCategory ? ['category' => $activeCategory->slug] : []) }}"
                data-infinite-scroll-has-more="{{ $artworks->hasMorePages() ? 'true' : 'false' }}"
                data-infinite-scroll-page="2"
            >
                @foreach ($artworks as $artwork)
                    <x-stitch.artwork-card :artwork="$artwork" />
                @endforeach
            </div>

            <div class="stitch-infinite-scroll-status mt-20 text-center" aria-live="polite">
                @if ($artworks->hasMorePages())
                    <div class="stitch-scroll-loader flex items-center justify-center gap-3 py-8">
                        <svg class="stitch-spinner h-6 w-6 animate-spin text-primary/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="font-body text-sm text-on-surface-variant/40">Загрузка...</span>
                    </div>
                    <div class="stitch-scroll-sentinel h-4" data-infinite-scroll-sentinel></div>
                @else
                    <div class="stitch-scroll-end py-16">
                        <div class="mx-auto flex items-center justify-center space-x-4">
                            <div class="stitch-ornament-line w-16"></div>
                            <span class="material-symbols-outlined text-2xl text-primary/30">blur_on</span>
                            <div class="stitch-ornament-line w-16"></div>
                        </div>
                        <p class="mt-6 font-body text-sm text-on-surface-variant/30">Все работы этого раздела перед вами</p>
                    </div>
                @endif
            </div>
        @else
            <div class="border border-outline/10 bg-surface-container px-8 py-16 text-center text-on-surface-variant">
                В этом разделе пока нет опубликованных работ.
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-7xl border-t border-outline/10 px-margin-mobile py-section-gap text-center md:px-margin-desktop">
        <div class="mx-auto max-w-3xl space-y-12">
            <span class="material-symbols-outlined text-4xl text-primary" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
            <blockquote class="font-headline text-[32px] leading-relaxed text-primary italic">
                «Её мазки — это не просто краска; это шепот минувшего века, отголоски печали и великолепия души, находящей красоту в тенях луны».
            </blockquote>
            <a href="{{ route('home') }}#contact" class="stitch-btn-outline">
                Связаться
            </a>
        </div>
    </section>

    <x-stitch.footer />
</x-layouts.stitch>
