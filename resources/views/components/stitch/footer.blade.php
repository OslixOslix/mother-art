<footer class="w-full border-t border-outline/5 bg-surface-container-lowest py-section-gap">
    <div class="mx-auto flex max-w-7xl flex-col items-center space-y-16 px-margin-mobile text-center md:px-margin-desktop md:space-y-20">
        <div class="space-y-6">
            <h2 class="font-headline text-5xl tracking-tight text-on-surface">Елена Буркальцева</h2>
            <p class="mx-auto max-w-lg font-headline text-2xl leading-snug text-primary/60 italic">
                «Душа — это безмолвная залитая лунным светом галерея».
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-x-14 gap-y-6">
            <a href="{{ route('gallery.index') }}" class="stitch-nav-link tracking-[0.2em] text-on-surface-variant/60 hover:text-primary">Галерея</a>
            <a href="{{ route('home') }}#about" class="stitch-nav-link tracking-[0.2em] text-on-surface-variant/60 hover:text-primary">Об авторе</a>
            <a href="{{ route('home') }}#contact" class="stitch-nav-link tracking-[0.2em] text-on-surface-variant/60 hover:text-primary">Контакты</a>
        </div>

        <div class="h-px w-full max-w-md bg-gradient-to-r from-transparent via-outline/10 to-transparent"></div>

        <p class="font-body text-[13px] tracking-[0.15em] text-on-surface-variant/30 uppercase">
            © {{ date('Y') }} Елена Буркальцева. Искусство как отражение вечности.
        </p>
    </div>
</footer>
