<!doctype html>
<html lang="ru" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Елена Буркальцева' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-background font-body text-base text-on-surface antialiased selection:bg-primary/30">
    <x-stitch.nav :active="$activeNav ?? null" :categories="$categories ?? null" />

    <main>
        @if (session('success'))
            <div class="mx-auto max-w-7xl px-margin-mobile pt-28 md:px-margin-desktop">
                <div class="border border-primary/30 bg-surface-container px-6 py-4 text-sm text-on-surface">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
