function initMobileDrawer() {
    const toggle = document.querySelector('[data-mobile-drawer-toggle]');
    const drawer = document.querySelector('[data-mobile-drawer]');
    const overlay = document.querySelector('[data-mobile-drawer-overlay]');

    if (! toggle || ! drawer || ! overlay) {
        return;
    }

    const closeDrawer = () => {
        drawer.classList.add('-translate-x-full', 'pointer-events-none');
        drawer.classList.remove('translate-x-0');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        overlay.classList.remove('opacity-100');
        toggle.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const openDrawer = () => {
        drawer.classList.remove('-translate-x-full', 'pointer-events-none');
        drawer.classList.add('translate-x-0');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
        toggle.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    toggle.addEventListener('click', () => {
        if (drawer.classList.contains('-translate-x-full')) {
            openDrawer();
        } else {
            closeDrawer();
        }
    });

    overlay.addEventListener('click', closeDrawer);

    drawer.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeDrawer);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });

    window.addEventListener('resize', () => {
        closeDrawer();
    });

    closeDrawer();
}

initMobileDrawer();
