let activeController = null;
let searchTimeout = null;

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

    return `<span class="flex h-full w-full items-center justify-center bg-primary/15 text-xs font-bold text-primary">${escapeHtml(user.avatar_initial)}</span>`;
}

export function initMentionAutocomplete(textarea, searchUrl) {
    if (! textarea || ! searchUrl || textarea.dataset.mentionBound) {
        return;
    }

    textarea.dataset.mentionBound = 'true';

    const wrapper = document.createElement('div');
    wrapper.className = 'relative';
    textarea.parentNode.insertBefore(wrapper, textarea);
    wrapper.appendChild(textarea);

    const dropdown = document.createElement('div');
    dropdown.className = 'absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg';
    dropdown.setAttribute('role', 'listbox');
    wrapper.appendChild(dropdown);

    let users = [];
    let selectedIndex = 0;
    let mentionStart = null;

    function closeDropdown() {
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
        users = [];
        selectedIndex = 0;
        mentionStart = null;
    }

    function getMentionContext() {
        const value = textarea.value;
        const cursor = textarea.selectionStart;
        const before = value.slice(0, cursor);
        const match = before.match(/(^|[^a-zA-Z0-9])@([a-z0-9-]*)$/i);

        if (! match) {
            return null;
        }

        return {
            start: before.lastIndexOf('@'),
            query: match[2].toLowerCase(),
        };
    }

    function renderDropdown() {
        if (users.length === 0) {
            dropdown.innerHTML = `
                <div class="px-4 py-3 text-sm text-slate-500">No users found</div>
            `;
            dropdown.classList.remove('hidden');

            return;
        }

        dropdown.innerHTML = users.map((user, index) => `
            <button
                type="button"
                role="option"
                data-mention-index="${index}"
                class="flex w-full items-center gap-3 px-3 py-2 text-left transition-colors ${index === selectedIndex ? 'bg-slate-100' : 'hover:bg-slate-50'}"
            >
                <div class="h-8 w-8 shrink-0 overflow-hidden rounded-full">
                    ${renderAvatar(user)}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">@${escapeHtml(user.handle)}</p>
                    <p class="truncate text-xs text-slate-500">${escapeHtml(user.name)}</p>
                </div>
            </button>
        `).join('');

        dropdown.classList.remove('hidden');

        dropdown.querySelectorAll('[data-mention-index]').forEach((button) => {
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
                selectUser(Number(button.getAttribute('data-mention-index')));
            });
        });
    }

    async function searchUsers(query) {
        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeController.signal,
            });

            if (! response.ok) {
                closeDropdown();

                return;
            }

            const data = await response.json();
            users = data.users ?? [];
            selectedIndex = 0;
            renderDropdown();
        } catch (error) {
            if (error.name !== 'AbortError') {
                closeDropdown();
            }
        }
    }

    function selectUser(index) {
        const user = users[index];

        if (! user || mentionStart === null) {
            closeDropdown();

            return;
        }

        const value = textarea.value;
        const before = value.slice(0, mentionStart);
        const after = value.slice(textarea.selectionStart);
        const mention = `@${user.handle} `;
        textarea.value = before + mention + after;
        const cursor = before.length + mention.length;
        textarea.setSelectionRange(cursor, cursor);
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        closeDropdown();
        textarea.focus();
    }

    function handleInput() {
        const context = getMentionContext();

        if (! context) {
            closeDropdown();

            return;
        }

        mentionStart = context.start;

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchUsers(context.query);
        }, 200);
    }

    textarea.addEventListener('input', handleInput);

    textarea.addEventListener('keydown', (event) => {
        if (dropdown.classList.contains('hidden') || users.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            selectedIndex = (selectedIndex + 1) % users.length;
            renderDropdown();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            selectedIndex = (selectedIndex - 1 + users.length) % users.length;
            renderDropdown();
        } else if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            selectUser(selectedIndex);
        } else if (event.key === 'Escape') {
            closeDropdown();
        }
    });

    textarea.addEventListener('blur', () => {
        setTimeout(closeDropdown, 150);
    });
}

export function initMentionInputs(searchUrl) {
    if (! searchUrl) {
        return;
    }

    document.querySelectorAll('[data-mention-input]').forEach((textarea) => {
        initMentionAutocomplete(textarea, searchUrl);
    });
}
