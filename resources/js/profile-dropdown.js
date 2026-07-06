export function initProfileDropdowns() {
    document.querySelectorAll('[data-profile-dropdown]').forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-profile-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-profile-dropdown-menu]');

        if (! toggle || ! menu) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = ! menu.classList.contains('hidden');

            closeAllProfileDropdowns();

            if (! isOpen) {
                menu.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', closeAllProfileDropdowns);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllProfileDropdowns();
        }
    });
}

function closeAllProfileDropdowns() {
    document.querySelectorAll('[data-profile-dropdown-menu]').forEach((menu) => {
        menu.classList.add('hidden');
    });

    document.querySelectorAll('[data-profile-dropdown-toggle]').forEach((toggle) => {
        toggle.setAttribute('aria-expanded', 'false');
    });
}

initProfileDropdowns();
