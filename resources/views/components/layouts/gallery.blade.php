<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Галерея работ художника' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-950 text-stone-100 antialiased">
    <header class="border-b border-white/10 bg-stone-950/95">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-6 md:flex-row md:items-end md:justify-between">
            <a href="{{ route('gallery.index') }}" class="block">
                <span class="text-sm uppercase text-amber-300">Mother Art</span>
                <span class="mt-2 block text-3xl font-semibold text-white">Галерея работ</span>
            </a>
            @isset($categories)
                <nav class="flex flex-wrap gap-2">
                    <a class="gallery-nav-link {{ empty($activeCategory) ? 'is-active' : '' }}" href="{{ route('gallery.index') }}">Все</a>
                    @foreach ($categories as $category)
                        <a class="gallery-nav-link {{ isset($activeCategory) && $activeCategory?->is($category) ? 'is-active' : '' }}" href="{{ route('gallery.category', $category) }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </nav>
            @endisset
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mx-auto max-w-7xl px-5 pt-6">
                <div class="border border-emerald-300/30 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
