const VIEW_KEY = 'posts-view';
const VALID_VIEWS = ['grid', 'list'];

function getInitialView() {
    const urlView = new URLSearchParams(window.location.search).get('view');

    if (VALID_VIEWS.includes(urlView)) {
        return urlView;
    }

    const storedView = localStorage.getItem(VIEW_KEY);

    if (VALID_VIEWS.includes(storedView)) {
        return storedView;
    }

    return 'grid';
}

function updateToggleButtons(view) {
    document.querySelectorAll('[data-posts-view-toggle]').forEach((button) => {
        const buttonView = button.getAttribute('data-posts-view-toggle');
        const isActive = buttonView === view;

        button.setAttribute('aria-pressed', String(isActive));
        button.classList.toggle('bg-slate-900', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('border-slate-900', isActive);
        button.classList.toggle('shadow-sm', isActive);
        button.classList.toggle('bg-white', ! isActive);
        button.classList.toggle('text-slate-600', ! isActive);
        button.classList.toggle('border-slate-200', ! isActive);
        button.classList.toggle('hover:border-blue-300', ! isActive);
        button.classList.toggle('hover:text-primary', ! isActive);
    });
}

export function setPostsView(view) {
    if (! VALID_VIEWS.includes(view)) {
        return;
    }

    const feed = document.getElementById('posts-feed');

    if (! feed) {
        return;
    }

    feed.setAttribute('data-posts-view', view);
    localStorage.setItem(VIEW_KEY, view);
    updateToggleButtons(view);
}

function initPostsViewToggle() {
    const feed = document.getElementById('posts-feed');

    if (! feed) {
        return;
    }

    setPostsView(getInitialView());

    document.querySelectorAll('[data-posts-view-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const view = button.getAttribute('data-posts-view-toggle');

            if (view) {
                setPostsView(view);
            }
        });
    });
}

initPostsViewToggle();
