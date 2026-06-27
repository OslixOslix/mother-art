import './artwork-zoom.js';

// --- Hero carousel rotation ---
function initHeroCarousel() {
    const container = document.getElementById('hero-carousel');
    if (!container) return;

    const slides = container.querySelectorAll('.stitch-hero-slide');
    const interval = parseInt(container.dataset.interval, 10) || 3000;

    if (slides.length < 2) return;

    let current = 0;
    let timer = null;

    function show(index) {
        const outgoing = slides[current];
        const incoming = slides[index];

        if (outgoing === incoming) return;

        incoming.classList.add('is-visible');
        outgoing.classList.remove('is-visible');
        outgoing.classList.add('is-fading-out');

        const onTransitionEnd = () => {
            outgoing.classList.remove('is-fading-out');
            outgoing.removeEventListener('transitionend', onTransitionEnd);
        };
        outgoing.addEventListener('transitionend', onTransitionEnd);

        current = index;
    }

    timer = setInterval(() => {
        const next = (current + 1) % slides.length;
        show(next);
    }, interval);

    // Pause on hover
    container.addEventListener('mouseenter', () => clearInterval(timer));
    container.addEventListener('mouseleave', () => {
        timer = setInterval(() => {
            const next = (current + 1) % slides.length;
            show(next);
        }, interval);
    });
}

// --- Scroll reveal ---
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

// --- Scroll-fill: image grows as the gallery item enters viewport ---
function initScrollFill() {
    const items = document.querySelectorAll('.stitch-fill-image');
    if (items.length === 0) return;

    const fillObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const ratio = entry.intersectionRatio;
            const el = entry.target;
            const frame = el.closest('.stitch-passe-partout-frame');

            if (ratio > 0) {
                const clamped = Math.min(ratio / 0.6, 1);
                const scale = 1 + clamped * 0.12;
                el.style.transform = `scale(${scale.toFixed(3)})`;
                el.style.opacity = 0.7 + clamped * 0.3;
                el.classList.add('is-filling');

                if (frame) {
                    const padding = Math.round(24 - clamped * 14);
                    frame.style.padding = `${padding}px`;
                }
            }
        });
    }, {
        threshold: Array.from({ length: 21 }, (_, i) => i / 20),
        rootMargin: '-10% 0px -10% 0px',
    });

    items.forEach((el) => fillObserver.observe(el));
}

// --- Parallax: image shifts as gallery item moves through viewport ---
function initParallax() {
    const items = document.querySelectorAll('.stitch-parallax-inner');
    if (items.length === 0) return;

    const parallaxObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const el = entry.target;
            const rect = el.closest('.stitch-gallery-item')?.getBoundingClientRect();
            if (!rect) return;

            const viewportHeight = window.innerHeight;
            const center = rect.top + rect.height / 2;
            const viewportCenter = viewportHeight / 2;
            const offset = (center - viewportCenter) / viewportCenter;
            const clamped = Math.max(-1, Math.min(1, offset));
            const translateY = clamped * 12;

            el.style.transform = `translateY(${translateY.toFixed(1)}px)`;
        });
    }, {
        threshold: Array.from({ length: 21 }, (_, i) => i / 20),
        rootMargin: '0px',
    });

    items.forEach((el) => parallaxObserver.observe(el));
}

// --- 3D tilt on hover for gallery cards ---
function initTilt() {
    const containers = document.querySelectorAll('.stitch-tilt-container');
    if (containers.length === 0) return;

    containers.forEach((container) => {
        const card = container.querySelector('.stitch-tilt-card');
        if (!card) return;

        container.addEventListener('mousemove', (e) => {
            const rect = container.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            card.style.transform = `rotateY(${(x * 6).toFixed(1)}deg) rotateX(${(-y * 6).toFixed(1)}deg)`;
            card.style.boxShadow = `
                ${-x * 10}px ${-y * 10}px 30px rgba(0, 0, 0, 0.45),
                inset 0 0 40px rgba(0, 0, 0, 0.05),
                0 20px 40px rgba(0, 0, 0, 0.4)
            `;
        });

        container.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateY(0deg) rotateX(0deg)';
            card.style.boxShadow = '';
            card.style.transition = 'transform 0.6s ease, box-shadow 0.6s ease';
            card.addEventListener('transitionend', () => {
                card.style.transition = 'transform 0.1s ease-out, box-shadow 0.4s ease';
            }, { once: true });
        });
    });
}

// --- Gallery hero animation ---
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

    initHeroCarousel();
    initScrollFill();
    initParallax();
    initTilt();
});

// --- Nav compression on scroll ---
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

// --- Horizontal thumbnail scroll ---
const thumbnailStrip = document.querySelector('.stitch-thumbnail-strip');

if (thumbnailStrip) {
    thumbnailStrip.addEventListener('wheel', (event) => {
        event.preventDefault();
        thumbnailStrip.scrollLeft += event.deltaY;
    });
}
