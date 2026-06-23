<x-layouts.stitch :title="'Заявка: '.$artwork->title" :active-nav="'gallery'">
    <section class="mx-auto max-w-5xl px-margin-mobile py-32 md:px-margin-desktop">
        <a href="{{ route('artworks.show', $artwork) }}" class="group mb-12 inline-flex items-center space-x-3 text-on-surface-variant/60 transition-colors hover:text-primary">
            <span class="material-symbols-outlined text-sm">arrow_back_ios</span>
            <span class="stitch-label">Вернуться к работе</span>
        </a>

        <div class="grid gap-12 lg:grid-cols-[0.85fr_1.15fr]">
            <aside>
                <div class="stitch-passe-partout-frame">
                    <div class="overflow-hidden bg-white/5">
                        @if ($artwork->imageUrl())
                            <img class="aspect-[4/5] w-full object-cover" src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}">
                        @else
                            <div class="flex aspect-[4/5] items-center justify-center text-on-surface-variant">Фото будет добавлено</div>
                        @endif
                    </div>
                </div>
                <h1 class="mt-6 font-headline text-3xl text-on-surface">{{ $artwork->title }}</h1>
                @if ($artwork->formattedPrice())
                    <p class="mt-2 font-body text-primary">{{ $artwork->formattedPrice() }}</p>
                @endif
            </aside>

            <div>
                <span class="stitch-label text-primary">Заявка</span>
                <h2 class="mt-4 mb-10 font-headline text-4xl text-on-surface">Связаться по работе</h2>

                <form class="space-y-10" method="post" action="{{ route('orders.store', $artwork) }}">
                    @csrf
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" value="">

                    <div class="relative group">
                        <input class="stitch-input peer" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" placeholder=" " required>
                        <label class="stitch-input-label" for="customer_name">Имя</label>
                        @error('customer_name')<span class="mt-2 block text-sm text-red-300">{{ $message }}</span>@enderror
                    </div>

                    <div class="grid gap-10 sm:grid-cols-2">
                        <div class="relative group">
                            <input class="stitch-input peer" id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder=" ">
                            <label class="stitch-input-label" for="customer_email">Email</label>
                            @error('customer_email')<span class="mt-2 block text-sm text-red-300">{{ $message }}</span>@enderror
                        </div>
                        <div class="relative group">
                            <input class="stitch-input peer" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder=" ">
                            <label class="stitch-input-label" for="customer_phone">Телефон</label>
                            @error('customer_phone')<span class="mt-2 block text-sm text-red-300">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="relative group">
                        <textarea class="stitch-input peer min-h-28" id="message" name="message" placeholder=" ">{{ old('message') }}</textarea>
                        <label class="stitch-input-label" for="message">Сообщение</label>
                        @error('message')<span class="mt-2 block text-sm text-red-300">{{ $message }}</span>@enderror
                    </div>

                    @if ($errors->has('customer_email') || $errors->has('customer_phone'))
                        <p class="text-sm text-red-300">Укажите email или телефон.</p>
                    @endif

                    <button class="stitch-btn-inquire" type="submit">
                        Отправить заявку
                    </button>
                </form>
            </div>
        </div>
    </section>

    <x-stitch.footer />
</x-layouts.stitch>
