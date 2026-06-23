// Увеличенный просмотр 1:1 при движении курсора по картине на странице работы
const canArtworkZoom = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

const artworkZoomPanels = new WeakMap();

const getArtworkZoomPanel = (wrapper) => artworkZoomPanels.get(wrapper) ?? null;

const loadArtworkZoomFullImage = (full) => {
    const zoomSrc = full.dataset.zoomSrc;

    if (!zoomSrc || full.dataset.loaded === 'true') {
        return Promise.resolve(full);
    }

    if (full.dataset.loading === 'true') {
        return new Promise((resolve) => {
            full.addEventListener('load', () => resolve(full), { once: true });
        });
    }

    full.dataset.loading = 'true';

    return new Promise((resolve) => {
        full.addEventListener('load', () => {
            full.dataset.loaded = 'true';
            full.dataset.loading = 'false';
            resolve(full);
        }, { once: true });
        full.src = zoomSrc;
    });
};

const updateArtworkZoom = (wrapper, event) => {
    const source = wrapper.querySelector('.stitch-artwork-zoom-source');
    const panel = getArtworkZoomPanel(wrapper);
    const full = panel?.querySelector('.stitch-artwork-zoom-full');
    const viewport = panel?.querySelector('.stitch-artwork-zoom-viewport');
    const indicator = wrapper.querySelector('.stitch-artwork-zoom-indicator');

    if (!source || !full || !viewport || !indicator || !source.naturalWidth || !full.naturalWidth) {
        return;
    }

    const rect = source.getBoundingClientRect();
    const x = clamp(event.clientX - rect.left, 0, rect.width);
    const y = clamp(event.clientY - rect.top, 0, rect.height);

    const fullNaturalWidth = full.naturalWidth;
    const fullNaturalHeight = full.naturalHeight;
    const naturalX = (x / rect.width) * fullNaturalWidth;
    const naturalY = (y / rect.height) * fullNaturalHeight;

    const viewportWidth = viewport.clientWidth;
    const viewportHeight = viewport.clientHeight;

    full.style.width = `${fullNaturalWidth}px`;
    full.style.height = `${fullNaturalHeight}px`;

    const translateX = clamp(viewportWidth / 2 - naturalX, viewportWidth - fullNaturalWidth, 0);
    const translateY = clamp(viewportHeight / 2 - naturalY, viewportHeight - fullNaturalHeight, 0);

    full.style.transform = `translate(${translateX}px, ${translateY}px)`;

    const ratioX = fullNaturalWidth / rect.width;
    const ratioY = fullNaturalHeight / rect.height;
    const indicatorWidth = viewportWidth / ratioX;
    const indicatorHeight = viewportHeight / ratioY;
    const indicatorX = clamp(x - indicatorWidth / 2, 0, rect.width - indicatorWidth);
    const indicatorY = clamp(y - indicatorHeight / 2, 0, rect.height - indicatorHeight);

    indicator.style.width = `${indicatorWidth}px`;
    indicator.style.height = `${indicatorHeight}px`;
    indicator.style.transform = `translate(${indicatorX}px, ${indicatorY}px)`;
    indicator.hidden = false;
};

const positionArtworkZoomPanel = (wrapper) => {
    const panel = getArtworkZoomPanel(wrapper);
    const artworkFrame = wrapper.closest('.stitch-passe-partout-artwork');
    const anchor = artworkFrame ?? wrapper;

    if (!panel || !anchor) {
        return;
    }

    const rect = anchor.getBoundingClientRect();
    const panelWidth = panel.offsetWidth;
    const viewportPadding = 16;

    // Чуть выше и правее основного изображения, с заходом на заголовок
    const top = rect.top - 32;
    const left = clamp(
        rect.right - panelWidth * 0.18,
        viewportPadding,
        window.innerWidth - panelWidth - viewportPadding,
    );

    panel.style.top = `${top}px`;
    panel.style.left = `${left}px`;
};

const activateArtworkZoom = (wrapper) => {
    const panel = getArtworkZoomPanel(wrapper);

    wrapper.classList.add('is-active');

    if (panel) {
        positionArtworkZoomPanel(wrapper);
        panel.classList.add('is-visible');
        panel.setAttribute('aria-hidden', 'false');
    }
};

const deactivateArtworkZoom = (wrapper) => {
    const panel = getArtworkZoomPanel(wrapper);
    const indicator = wrapper.querySelector('.stitch-artwork-zoom-indicator');

    wrapper.classList.remove('is-active');

    if (panel) {
        panel.classList.remove('is-visible');
        panel.setAttribute('aria-hidden', 'true');
    }

    if (indicator) {
        indicator.hidden = true;
    }
};

if (canArtworkZoom) {
    const activeWrappers = new Set();

    document.querySelectorAll('[data-artwork-zoom]').forEach((wrapper) => {
        const source = wrapper.querySelector('.stitch-artwork-zoom-source');
        const panel = wrapper.querySelector('.stitch-artwork-zoom-panel');

        if (!source || !panel) {
            return;
        }

        // Панель в body — иначе правая колонка с текстом перекрывает превью
        document.body.appendChild(panel);
        artworkZoomPanels.set(wrapper, panel);

        const full = panel.querySelector('.stitch-artwork-zoom-full');

        const handleMove = (event) => {
            updateArtworkZoom(wrapper, event);
        };

        const handleEnter = async (event) => {
            if (full) {
                await loadArtworkZoomFullImage(full);
            }

            activeWrappers.add(wrapper);
            activateArtworkZoom(wrapper);
            updateArtworkZoom(wrapper, event);
        };

        const handleLeave = () => {
            activeWrappers.delete(wrapper);
            deactivateArtworkZoom(wrapper);
        };

        source.addEventListener('mouseenter', handleEnter);
        source.addEventListener('mousemove', handleMove);
        source.addEventListener('mouseleave', handleLeave);
    });

    const repositionActivePanels = () => {
        activeWrappers.forEach((wrapper) => {
            positionArtworkZoomPanel(wrapper);
        });
    };

    window.addEventListener('resize', repositionActivePanels);
    window.addEventListener('scroll', repositionActivePanels, { passive: true });
}
