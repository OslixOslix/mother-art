@props(['active' => null, 'categories' => null])

<nav class="fixed top-0 z-50 w-full border-b border-outline/10 bg-background/80 backdrop-blur-xl">
    <div
        id="stitch-nav-container"
        class="mx-auto flex max-w-7xl items-center justify-between px-margin-mobile py-6 transition-all duration-700 md:px-margin-desktop"
    >
        <a href="{{ route('home') }}" class="font-headline text-2xl tracking-tight text-on-surface md:text-[32px]">
            Елена Буркальцева
        </a>

        <div class="hidden items-center gap-10 md:flex">
            <a
                href="{{ route('home') }}"
                @class([
                    'stitch-nav-link',
                    'is-active is-active--underlined' => $active === 'home',
                ])
            >Главная</a>
            <a
                href="{{ route('gallery.index') }}"
                @class([
                    'stitch-nav-link',
                    'is-active is-active--underlined' => $active === 'gallery',
                ])
            >Галерея</a>
            @if ($categories?->isNotEmpty())
                @foreach ($categories->take(2) as $category)
                    <a
                        href="{{ route('gallery.category', $category) }}"
                        @class([
                            'stitch-nav-link',
                            'is-active' => $active === 'category-'.$category->id,
                        ])
                    >{{ $category->name }}</a>
                @endforeach
            @endif
            <a href="{{ route('home') }}#contact" class="stitch-nav-link">Контакты</a>
        </div>

        <button type="button" class="text-primary md:hidden" aria-label="Меню">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
</nav>
