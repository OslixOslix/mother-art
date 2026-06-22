<x-layouts.gallery :title="'Заявка: ' . $artwork->title">
    <section class="mx-auto grid max-w-5xl gap-8 px-5 py-10 md:grid-cols-[0.85fr_1.15fr] md:py-14">
        <aside class="border border-white/10 bg-stone-900 p-4">
            <div class="aspect-[4/5] bg-stone-800">
                @if ($artwork->imageUrl())
                    <img class="h-full w-full object-cover" src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}">
                @endif
            </div>
            <h1 class="mt-4 text-2xl font-semibold text-white">{{ $artwork->title }}</h1>
            @if ($artwork->formattedPrice())
                <p class="mt-2 text-amber-200">{{ $artwork->formattedPrice() }}</p>
            @endif
        </aside>

        <div>
            <p class="text-sm uppercase text-amber-300">Заявка</p>
            <h2 class="mt-3 text-4xl font-semibold text-white">Связаться по работе</h2>

            <form class="mt-8 space-y-5" method="post" action="{{ route('orders.store', $artwork) }}">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" value="">

                <label class="block">
                    <span class="gallery-label">Имя</span>
                    <input class="gallery-input" name="customer_name" value="{{ old('customer_name') }}" required>
                    @error('customer_name')<span class="gallery-error">{{ $message }}</span>@enderror
                </label>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block">
                        <span class="gallery-label">Email</span>
                        <input class="gallery-input" type="email" name="customer_email" value="{{ old('customer_email') }}">
                        @error('customer_email')<span class="gallery-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="gallery-label">Телефон</span>
                        <input class="gallery-input" name="customer_phone" value="{{ old('customer_phone') }}">
                        @error('customer_phone')<span class="gallery-error">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="block">
                    <span class="gallery-label">Сообщение</span>
                    <textarea class="gallery-input min-h-36" name="message">{{ old('message') }}</textarea>
                    @error('message')<span class="gallery-error">{{ $message }}</span>@enderror
                </label>

                @if ($errors->has('customer_email') || $errors->has('customer_phone'))
                    <p class="gallery-error">Укажите email или телефон.</p>
                @endif

                <button class="inline-flex w-full items-center justify-center bg-amber-300 px-5 py-3 text-sm font-semibold uppercase text-stone-950 transition hover:bg-amber-200 sm:w-auto" type="submit">
                    Отправить заявку
                </button>
            </form>
        </div>
    </section>
</x-layouts.gallery>
