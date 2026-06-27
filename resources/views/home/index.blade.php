<x-layouts.stitch :title="'Елена Буркальцева | Портфолио художника'" :active-nav="'home'">
    {{-- Hero --}}
    <section class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-margin-mobile pt-32 md:px-margin-desktop">
        <div class="absolute inset-0 z-0 opacity-30">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-primary/10 via-background to-background"></div>
        </div>

        <div class="relative z-10 flex w-full max-w-7xl flex-col items-center gap-20 md:flex-row">
            <div class="stitch-reveal w-full md:w-5/12">
                <span class="stitch-label mb-8 block tracking-[0.4em] text-primary">Художник-визионер</span>
                <div class="stitch-hero-text-wrap mb-10">
                    <h1 class="font-headline text-5xl leading-[1.05] text-on-surface italic md:text-[64px]">Тяжесть лунного света</h1>
                </div>
                <div class="stitch-hero-text-wrap mb-14">
                    <p class="max-w-md font-body text-lg font-light leading-relaxed text-on-surface-variant opacity-90">
                        Исследование пересечений памяти и мифа сквозь призму русской поэзии начала XX века. Каждый мазок — безмолвный слог в бесконечном диалоге с вечностью.
                    </p>
                </div>
                <a href="{{ route('gallery.index') }}" class="stitch-btn-outline">
                    Смотреть коллекцию
                </a>
            </div>

            <div class="stitch-reveal w-full md:w-7/12" id="hero-carousel" data-interval="6000">
                <div class="group relative mx-auto max-w-lg md:ml-auto">
                    <div class="stitch-passe-partout">
                        <div class="stitch-passe-partout-inner stitch-hero-grid overflow-hidden">
                            @php $heroArtworks = $heroArtworks->shuffle(); @endphp
                            @if ($heroArtworks->isNotEmpty())
                                @foreach ($heroArtworks as $index => $heroArt)
                                    @php $heroImg = $heroArt->imageUrl(\App\Enums\ArtworkImagePreset::Hero); @endphp
                                    <a href="{{ route('artworks.show', $heroArt) }}" class="stitch-hero-slide block {{ $index === 0 ? 'is-visible' : '' }}" data-index="{{ $index }}" data-title="{{ $heroArt->title }}" data-description="{{ $heroArt->description ?: 'Исследование пересечений памяти и мифа сквозь призму русской поэзии начала XX века. Каждый мазок — безмолвный слог в бесконечном диалоге с вечностью.' }}">
                                        <img
                                            class="aspect-[4/5] w-full object-cover grayscale-[0.1] transition-all duration-[2s] group-hover:grayscale-0"
                                            src="{{ $heroImg }}"
                                            alt="{{ $heroArt->title }}"
                                        >
                                    </a>
                                @endforeach
                            @else
                                <img
                                    class="aspect-[4/5] w-full object-cover grayscale-[0.1] transition-all duration-[2s] group-hover:grayscale-0"
                                    src="{{ asset('images/stitch/spirit-of-twilight.jpg') }}"
                                    alt="Главная работа"
                                >
                            @endif
                        </div>
                    </div>
                    <div class="absolute -right-12 -bottom-12 -z-10 h-48 w-48 border-r border-b border-primary/10 transition-transform duration-1000 group-hover:scale-110"></div>
                    <div class="absolute -top-12 -left-12 -z-10 h-48 w-48 border-t border-l border-primary/10 transition-transform duration-1000 group-hover:scale-110"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Избранное --}}
    @if ($featuredArtworks->isNotEmpty())
        <section class="mx-auto max-w-7xl px-margin-mobile py-section-gap md:px-margin-desktop" id="gallery">
            <div class="stitch-reveal mb-28 flex flex-col items-start justify-between md:flex-row md:items-end">
                <div class="mb-8 md:mb-0">
                    <span class="stitch-label mb-5 block tracking-[0.3em] text-primary">Портфолио</span>
                    <h2 class="font-headline text-5xl text-on-surface">Избранное эхо</h2>
                </div>
                <a href="{{ route('gallery.index') }}" class="stitch-nav-link flex items-center gap-3 border-b border-outline/10 pb-1 hover:border-primary">
                    <span>Перейти в полную галерею</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-x-16 gap-y-24 md:grid-cols-12">
                @foreach ($featuredArtworks as $index => $artwork)
                    @if ($index === 0)
                        <x-stitch.artwork-card :artwork="$artwork" variant="featured-large" />
                    @elseif ($index === 1)
                        <x-stitch.artwork-card :artwork="$artwork" variant="featured-side" :stagger="200" />
                    @else
                        <x-stitch.artwork-card :artwork="$artwork" variant="featured-small" :stagger="($index - 1) * 100" />
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- О авторе --}}
    <section class="relative overflow-hidden bg-surface-container-lowest py-section-gap" id="about">
        <div class="stitch-reveal mx-auto max-w-4xl px-margin-mobile text-center">
            <span class="stitch-label mb-14 block tracking-[0.5em] text-primary italic">Философия творчества</span>
            <h2 class="mx-auto mb-14 max-w-3xl font-headline text-5xl leading-[1.3] text-on-surface italic">
                Меня давно привлекает Серебряный век — не как готовый стиль, а как настроение: немного печальное, тонкое, внимательное к человеку и его внутренней жизни.
            </h2>
            <p class="mx-auto mb-16 max-w-2xl font-body text-lg font-light leading-relaxed text-on-surface-variant opacity-90">
                В живописи я люблю работать постепенно, слой за слоем. Полупрозрачные глазури помогают мне создавать глубину и мягкий свет. Мне интересно не столько само сияние, сколько то, как оно проявляется в тени, в памяти, в ощущении утраты — и всё равно остаётся красивым.
            </p>
            <a href="{{ route('gallery.index') }}" class="stitch-btn-primary">
                Смотреть коллекцию
            </a>
        </div>
    </section>

    {{-- Контакты --}}
    <section class="mx-auto max-w-7xl px-margin-mobile py-section-gap md:px-margin-desktop" id="contact">
        <div class="grid gap-32 md:grid-cols-2">
            <div class="stitch-reveal">
                <h2 class="mb-10 font-headline text-5xl text-on-surface italic">Шепот в пустоту</h2>
                <p class="mb-14 font-body text-lg font-light leading-relaxed text-on-surface-variant opacity-80">
                    По вопросам приобретения работ из коллекции, частных заказов или если вы просто хотите поделиться созвучным стихотворением.
                </p>
                <div class="space-y-10">
                    <div class="group flex items-center gap-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full border border-primary/10 transition-all duration-500 group-hover:border-primary/40">
                            <span class="material-symbols-outlined text-primary/60 group-hover:text-primary">mail</span>
                        </div>
                        <span class="font-body text-on-surface-variant transition-colors group-hover:text-on-surface">elena-burkaltseva@yandex.ru</span>
                    </div>

                </div>
            </div>

            <div class="stitch-reveal">
                <p class="mb-8 font-body text-on-surface-variant">
                    Чтобы оформить заявку на конкретную работу, откройте её страницу в галерее и нажмите «Узнать о наличии».
                </p>
                <a href="{{ route('gallery.index') }}" class="stitch-btn-outline w-full text-center">
                    Перейти в галерею
                </a>
            </div>
        </div>
    </section>

    <x-stitch.footer />
</x-layouts.stitch>
