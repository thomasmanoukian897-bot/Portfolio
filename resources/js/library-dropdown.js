function initLibraryDropdowns() {
    document.querySelectorAll('[data-library-dropdown]').forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-library-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-library-dropdown-menu]');
        const chevron = dropdown.querySelector('[data-library-dropdown-chevron]');

        if (! toggle || ! menu) {
            return;
        }

        const setOpen = (open) => {
            menu.classList.toggle('hidden', ! open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            chevron?.classList.toggle('rotate-180', open);
        };

        const isOpen = ! menu.classList.contains('hidden');
        setOpen(isOpen);

        toggle.addEventListener('click', () => {
            setOpen(menu.classList.contains('hidden'));
        });
    });
}

initLibraryDropdowns();
