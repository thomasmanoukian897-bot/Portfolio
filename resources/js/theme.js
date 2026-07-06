const THEME_KEY = 'theme';

export function getTheme() {
    if (localStorage.getItem(THEME_KEY)) {
        return localStorage.getItem(THEME_KEY);
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function setTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem(THEME_KEY, theme);
    updateThemeControls(theme);
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');

    setTheme(isDark ? 'light' : 'dark');
}

function updateThemeControls(theme) {
    const isDark = theme === 'dark';

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');

        button.querySelector('[data-theme-icon="light"]')?.classList.toggle('hidden', isDark);
        button.querySelector('[data-theme-icon="dark"]')?.classList.toggle('hidden', ! isDark);
    });

    document.querySelectorAll('[data-theme-slider]').forEach((slider) => {
        slider.checked = isDark;
        slider.setAttribute('aria-checked', isDark ? 'true' : 'false');
    });
}

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', toggleTheme);
});

document.querySelectorAll('[data-theme-slider]').forEach((slider) => {
    slider.addEventListener('change', (event) => {
        setTheme(event.target.checked ? 'dark' : 'light');
    });
});

updateThemeControls(getTheme());
