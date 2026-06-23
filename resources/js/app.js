import './artwork-zoom.js';

// Анимации появления элементов при скролле (Stitch design)
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -80px 0px',
};

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.stitch-reveal, .stitch-reveal-image, .stitch-gallery-item').forEach((el) => {
    revealObserver.observe(el);
});

// Анимация hero-секции галереи
window.addEventListener('DOMContentLoaded', () => {
    const title = document.getElementById('gallery-hero-title');
    const subtitle = document.getElementById('gallery-hero-subtitle');
    const ornament = document.getElementById('gallery-hero-ornament');

    if (title && subtitle && ornament) {
        setTimeout(() => {
            ornament.classList.remove('opacity-0');
            title.classList.remove('opacity-0', 'translate-y-10');
            subtitle.classList.remove('opacity-0');
        }, 400);
    }
});

// Сжатие навигации при скролле
const navContainer = document.getElementById('stitch-nav-container');

if (navContainer) {
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 100) {
            navContainer.classList.remove('py-6');
            navContainer.classList.add('py-4');
        } else {
            navContainer.classList.remove('py-4');
            navContainer.classList.add('py-6');
        }
    });
}

// Горизонтальный скролл миниатюр колёсиком мыши
const thumbnailStrip = document.querySelector('.stitch-thumbnail-strip');

if (thumbnailStrip) {
    thumbnailStrip.addEventListener('wheel', (event) => {
        event.preventDefault();
        thumbnailStrip.scrollLeft += event.deltaY;
    });
}
