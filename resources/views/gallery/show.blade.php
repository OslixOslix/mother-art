<x-layouts.gallery :categories="$categories" :active-category="$artwork->category" :title="$artwork->title">
    <section class="mx-auto grid max-w-7xl gap-8 px-5 py-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)] lg:py-14">
        <div class="bg-stone-900">
            @if ($artwork->imageUrl())
                <img class="h-auto w-full object-contain" src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}">
            @else
                <div class="flex aspect-[4/5] items-center justify-center text-stone-500">Фото будет добавлено</div>
            @endif
        </div>

        <div class="lg:sticky lg:top-8 lg:self-start">
            <p class="text-sm uppercase text-amber-300">{{ $artwork->category?->name }}</p>
            <h1 class="mt-4 text-4xl font-semibold text-white md:text-5xl">{{ $artwork->title }}</h1>

            @if ($artwork->formattedPrice())
                <p class="mt-5 text-2xl text-amber-200">{{ $artwork->formattedPrice() }}</p>
            @endif

            @if ($artwork->description)
                <div class="mt-8 max-w-none text-stone-300">
                    {!! nl2br(e($artwork->description)) !!}
                </div>
            @endif

            <a href="{{ route('orders.create', $artwork) }}" class="mt-8 inline-flex w-full items-center justify-center bg-amber-300 px-5 py-3 text-sm font-semibold uppercase text-stone-950 transition hover:bg-amber-200 sm:w-auto">
                Оформить заявку
            </a>
        </div>
    </section>
</x-layouts.gallery>
