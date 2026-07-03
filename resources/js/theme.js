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
    updateToggleButtons(theme);
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');

    setTheme(isDark ? 'light' : 'dark');
}

function updateToggleButtons(theme) {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const isDark = theme === 'dark';

        button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');

        button.querySelector('[data-theme-icon="light"]')?.classList.toggle('hidden', isDark);
        button.querySelector('[data-theme-icon="dark"]')?.classList.toggle('hidden', ! isDark);
    });
}

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', toggleTheme);
});

updateToggleButtons(getTheme());
