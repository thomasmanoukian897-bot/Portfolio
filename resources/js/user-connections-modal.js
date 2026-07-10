const modal = document.getElementById('user-connections-modal');
const titleEl = document.getElementById('user-connections-modal-title');
const searchInput = document.getElementById('user-connections-search');
const listEl = document.getElementById('user-connections-list');

let activeType = 'followers';
let searchTimeout = null;
let activeController = null;

function getEndpointUrl(type) {
    if (! modal) {
        return null;
    }

    return type === 'following'
        ? modal.getAttribute('data-following-url')
        : modal.getAttribute('data-followers-url');
}

function getTitle(type) {
    return type === 'following' ? 'Following' : 'Followers';
}

function escapeHtml(value) {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function renderAvatar(user) {
    if (user.avatar_url) {
        return `<img src="${escapeHtml(user.avatar_url)}" alt="" class="h-full w-full object-cover" />`;
    }

    return `<span class="flex h-full w-full items-center justify-center bg-primary/15 text-sm font-bold text-primary">${escapeHtml(user.avatar_initial)}</span>`;
}

function renderUser(user) {
    return `
        <a
            href="${escapeHtml(user.profile_url)}"
            class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/70"
            role="listitem"
        >
            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-full">
                ${renderAvatar(user)}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">${escapeHtml(user.handle)}</p>
                <p class="truncate text-sm text-slate-500 dark:text-slate-400">${escapeHtml(user.name)}</p>
            </div>
        </a>
    `;
}

function renderEmptyState(query) {
    const message = query
        ? 'No users match your search.'
        : `No ${activeType} yet.`;

    listEl.innerHTML = `
        <div class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
            ${escapeHtml(message)}
        </div>
    `;
}

function renderLoadingState() {
    listEl.innerHTML = `
        <div class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
            Loading...
        </div>
    `;
}

async function loadConnections(query = '') {
    const url = getEndpointUrl(activeType);

    if (! url || ! listEl) {
        return;
    }

    activeController?.abort();
    activeController = new AbortController();

    renderLoadingState();

    const endpoint = new URL(url, window.location.origin);

    if (query !== '') {
        endpoint.searchParams.set('search', query);
    }

    try {
        const response = await fetch(endpoint, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: activeController.signal,
        });

        if (! response.ok) {
            listEl.innerHTML = `
                <div class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                    Unable to load ${escapeHtml(activeType)}.
                </div>
            `;

            return;
        }

        const data = await response.json();
        const users = Array.isArray(data.users) ? data.users : [];

        if (users.length === 0) {
            renderEmptyState(query);

            return;
        }

        listEl.innerHTML = users.map(renderUser).join('');
    } catch (error) {
        if (error.name === 'AbortError') {
            return;
        }

        listEl.innerHTML = `
            <div class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                Unable to load ${escapeHtml(activeType)}.
            </div>
        `;
    }
}

function openModal(type) {
    if (! modal) {
        return;
    }

    activeType = type === 'following' ? 'following' : 'followers';

    if (titleEl) {
        titleEl.textContent = getTitle(activeType);
    }

    if (searchInput) {
        searchInput.value = '';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    loadConnections();

    window.setTimeout(() => {
        searchInput?.focus();
    }, 0);
}

function closeModal() {
    if (! modal) {
        return;
    }

    activeController?.abort();
    activeController = null;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function scheduleSearch() {
    window.clearTimeout(searchTimeout);

    searchTimeout = window.setTimeout(() => {
        loadConnections(searchInput?.value.trim() ?? '');
    }, 300);
}

document.querySelectorAll('[data-user-connections-open]').forEach((button) => {
    button.addEventListener('click', () => {
        openModal(button.getAttribute('data-user-connections-open') ?? 'followers');
    });
});

document.querySelectorAll('[data-user-connections-close]').forEach((element) => {
    element.addEventListener('click', closeModal);
});

searchInput?.addEventListener('input', scheduleSearch);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal && ! modal.classList.contains('hidden')) {
        closeModal();
    }
});
