<x-layouts.gallery :categories="$categories" :active-category="$activeCategory" :title="$activeCategory ? $activeCategory->name : 'Галерея работ художника'">
    <section class="mx-auto max-w-7xl px-5 py-10 md:py-14">
        <div class="mb-8 max-w-3xl">
            <p class="text-sm uppercase text-amber-300">{{ $activeCategory?->name ?? 'Все разделы' }}</p>
            <h1 class="mt-3 text-4xl font-semibold text-white md:text-6xl">Работы художника</h1>
        </div>

        @if ($artworks->count())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($artworks as $artwork)
                    <x-artwork-card :artwork="$artwork" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $artworks->links() }}
            </div>
        @else
            <div class="border border-white/10 bg-stone-900 px-6 py-12 text-stone-300">
                В этом разделе пока нет опубликованных работ.
            </div>
        @endif
    </section>
</x-layouts.gallery>
