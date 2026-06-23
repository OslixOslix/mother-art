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
            <div class="grid grid-cols-1 items-start gap-x-item-gap gap-y-32 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($artworks as $artwork)
                    <x-stitch.artwork-card :artwork="$artwork" />
                @endforeach
            </div>

            <div class="mt-20">
                {{ $artworks->links() }}
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
