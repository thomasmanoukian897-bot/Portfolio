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

function renderMessageBubble(message) {
    if (message.is_system) {
        return `
            <div class="flex justify-center py-1" data-message-id="${message.id}">
                <p class="text-xs text-slate-500 dark:text-slate-400 text-center px-3 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80">
                    ${escapeHtml(message.body)}
                </p>
            </div>
        `;
    }

    const isMine = message.is_mine;

    return `
        <div class="flex ${isMine ? 'justify-end' : 'justify-start'}" data-message-id="${message.id}">
            <div class="flex items-start gap-3 max-w-[85%] md:max-w-[70%] ${isMine ? 'flex-row-reverse' : ''}">
                ${isMine ? '' : `
                    <div class="shrink-0 rounded-full overflow-hidden flex items-center justify-center font-bold w-8 h-8 text-xs mt-0.5">
                        ${renderAvatar(message.user)}
                    </div>
                `}
                <div>
                    ${isMine ? '' : `<p class="text-xs text-slate-500 dark:text-slate-400 mb-1 px-1"><a href="${escapeHtml(message.user.profile_url)}" class="hover:text-primary transition-colors">${escapeHtml(message.user.name)}</a></p>`}
                    <div class="rounded-2xl px-4 py-2.5 text-sm leading-relaxed ${isMine ? 'bg-primary text-white rounded-br-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-bl-md'}">
                        <p class="whitespace-pre-wrap break-words">${escapeHtml(message.body)}</p>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1 px-1 ${isMine ? 'text-right' : ''}">${escapeHtml(message.created_at_label ?? '')}</p>
                </div>
            </div>
        </div>
    `;
}

function scrollMessagesToBottom(container) {
    if (! container) {
        return;
    }

    container.scrollTop = container.scrollHeight;
}

function initMessageComposer(root) {
    const form = root.querySelector('[data-messages-form]');
    const input = root.querySelector('[data-messages-input]');
    const submit = root.querySelector('[data-messages-submit]');
    const list = root.querySelector('[data-messages-list]');
    const sendUrl = root.dataset.sendUrl;
    const messagesUrl = root.dataset.messagesUrl;
    const csrf = root.dataset.csrf;

    if (! form || ! input || ! submit || ! list || ! sendUrl || ! csrf) {
        return;
    }

    let lastMessageId = Number([...list.querySelectorAll('[data-message-id]')].at(-1)?.getAttribute('data-message-id') ?? 0);
    let polling = false;

    function setSubmitState() {
        submit.disabled = input.value.trim() === '';
    }

    input.addEventListener('input', setSubmitState);

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && ! event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const body = input.value.trim();

        if (! body) {
            return;
        }

        submit.disabled = true;
        input.disabled = true;

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ body }),
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (data.message) {
                list.insertAdjacentHTML('beforeend', renderMessageBubble(data.message));
                lastMessageId = data.message.id;
                input.value = '';
                scrollMessagesToBottom(list);
            }
        } finally {
            input.disabled = false;
            setSubmitState();
            input.focus();
        }
    });

    async function pollMessages() {
        if (polling || ! messagesUrl) {
            return;
        }

        polling = true;

        try {
            const response = await fetch(`${messagesUrl}?after_id=${lastMessageId}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (data.messages?.length) {
                data.messages.forEach((message) => {
                    if (list.querySelector(`[data-message-id="${message.id}"]`)) {
                        return;
                    }

                    list.insertAdjacentHTML('beforeend', renderMessageBubble(message));
                    lastMessageId = message.id;
                });

                scrollMessagesToBottom(list);
            }
        } finally {
            polling = false;
        }
    }

    scrollMessagesToBottom(list);
    setSubmitState();
    input.focus();

    window.setInterval(pollMessages, 5000);
}

function initUserSearch(root) {
    const searchUrl = root.dataset.usersSearchUrl;

    if (! searchUrl) {
        return;
    }

    const dmModal = root.querySelector('[data-messages-dm-modal]');
    const groupModal = root.querySelector('[data-messages-group-modal]');
    const groupMembersModal = root.querySelector('[data-messages-group-members-modal]');
    const groupMembers = new Set();

    function closeModals() {
        [dmModal, groupModal, groupMembersModal].forEach((modal) => {
            if (! modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        document.body.style.overflow = '';
    }

    function openModal(modal) {
        if (! modal) {
            return;
        }

        closeModals();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        modal.querySelector('input[type="text"]')?.focus();
    }

    root.querySelectorAll('[data-messages-new-dm]').forEach((button) => {
        button.addEventListener('click', () => openModal(dmModal));
    });

    function resetGroupForm() {
        groupMembers.clear();

        const membersContainer = root.querySelector('[data-messages-group-members]');

        if (membersContainer) {
            membersContainer.innerHTML = '';
        }

        const nameInput = groupModal?.querySelector('#group-name');

        if (nameInput) {
            nameInput.value = '';
        }

        updateGroupSubmit();
    }

    root.querySelectorAll('[data-messages-new-group]').forEach((button) => {
        button.addEventListener('click', () => {
            resetGroupForm();
            openModal(groupModal);
        });
    });

    root.querySelectorAll('[data-messages-close-modal]').forEach((button) => {
        button.addEventListener('click', closeModals);
    });

    [dmModal, groupModal, groupMembersModal].forEach((modal) => {
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModals();
            }
        });
    });

    root.querySelector('[data-messages-group-members-open]')?.addEventListener('click', () => {
        openModal(groupMembersModal);
    });

    function updateDmSubmit() {
        const selectedId = dmModal?.querySelector('[data-messages-selected-user-id]')?.value;
        const submit = dmModal?.querySelector('[data-messages-dm-submit]');

        if (submit) {
            submit.disabled = ! selectedId;
        }
    }

    function isGroupFormValid() {
        const name = groupModal?.querySelector('#group-name')?.value.trim();

        return Boolean(name) && groupMembers.size > 0;
    }

    function updateGroupSubmit() {
        const submit = groupModal?.querySelector('[data-messages-group-submit]');
        const isValid = isGroupFormValid();

        if (submit) {
            submit.classList.toggle('opacity-40', ! isValid);
            submit.classList.toggle('cursor-not-allowed', ! isValid);
            submit.setAttribute('aria-disabled', String(! isValid));
        }
    }

    groupModal?.querySelector('#group-name')?.addEventListener('input', updateGroupSubmit);

    groupModal?.querySelector('form')?.addEventListener('submit', (event) => {
        if (! isGroupFormValid()) {
            event.preventDefault();
        }
    });

    function renderSelectedUser(target, user) {
        const container = root.querySelector(`[data-messages-selected-user="${target}"]`);

        if (! container) {
            return;
        }

        container.classList.remove('hidden');
        container.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="shrink-0 rounded-full overflow-hidden flex items-center justify-center font-bold w-8 h-8 text-xs">
                    ${renderAvatar(user)}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">${escapeHtml(user.name)}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">@${escapeHtml(user.handle)}</p>
                </div>
            </div>
        `;
    }

    function renderGroupMemberChip(user) {
        const container = root.querySelector('[data-messages-group-members]');

        if (! container) {
            return;
        }

        const chip = document.createElement('div');
        chip.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-sm';
        chip.dataset.memberId = String(user.id);
        chip.innerHTML = `
            <span class="text-slate-800 dark:text-slate-200">${escapeHtml(user.name)}</span>
            <button type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" aria-label="Remove ${escapeHtml(user.name)}">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
            <input type="hidden" name="user_ids[]" value="${user.id}" />
        `;

        chip.querySelector('button')?.addEventListener('click', () => {
            groupMembers.delete(user.id);
            chip.remove();
            updateGroupSubmit();
        });

        container.appendChild(chip);
    }

    let searchTimeout = null;

    root.querySelectorAll('[data-messages-user-search]').forEach((input) => {
        const target = input.dataset.messagesSearchTarget;
        const results = root.querySelector(`[data-messages-search-results="${target}"]`);

        input.addEventListener('input', () => {
            const query = input.value.trim();

            window.clearTimeout(searchTimeout);

            if (! results) {
                return;
            }

            if (query === '') {
                results.classList.add('hidden');
                results.innerHTML = '';

                return;
            }

            searchTimeout = window.setTimeout(async () => {
                if (! results) {
                    return;
                }
                const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                const users = data.users ?? [];

                if (users.length === 0) {
                    results.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">No users found</div>';
                    results.classList.remove('hidden');

                    return;
                }

                results.innerHTML = users.map((user) => `
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        data-user-id="${user.id}"
                        data-user-name="${escapeHtml(user.name)}"
                        data-user-handle="${escapeHtml(user.handle)}"
                        data-user-avatar-url="${escapeHtml(user.avatar_url ?? '')}"
                        data-user-avatar-initial="${escapeHtml(user.avatar_initial)}"
                    >
                        <div class="shrink-0 rounded-full overflow-hidden flex items-center justify-center font-bold w-8 h-8 text-xs">
                            ${renderAvatar(user)}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">${escapeHtml(user.name)}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">@${escapeHtml(user.handle)}</p>
                        </div>
                    </button>
                `).join('');

                results.classList.remove('hidden');

                results.querySelectorAll('button[data-user-id]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const user = {
                            id: Number(button.dataset.userId),
                            name: button.dataset.userName,
                            handle: button.dataset.userHandle,
                            avatar_url: button.dataset.userAvatarUrl || null,
                            avatar_initial: button.dataset.userAvatarInitial,
                        };

                        if (target === 'dm') {
                            const hiddenInput = dmModal?.querySelector('[data-messages-selected-user-id]');

                            if (hiddenInput) {
                                hiddenInput.value = String(user.id);
                            }

                            renderSelectedUser('dm', user);
                            updateDmSubmit();
                            input.value = '';
                            results.classList.add('hidden');
                            results.innerHTML = '';
                        }

                        if (target === 'group') {
                            if (groupMembers.has(user.id)) {
                                return;
                            }

                            groupMembers.add(user.id);
                            renderGroupMemberChip(user);
                            updateGroupSubmit();
                            input.value = '';
                            results.classList.add('hidden');
                            results.innerHTML = '';
                        }
                    });
                });
            }, 250);
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => {
                results?.classList.add('hidden');
            }, 150);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModals();
        }
    });

    if (root.dataset.reopenGroupModal === 'true') {
        openModal(groupModal);
        updateGroupSubmit();
    }
}

function initGroupAvatar(root) {
    const avatarInput = root.querySelector('[data-messages-group-avatar-input]');
    const avatarForm = root.querySelector('[data-messages-group-avatar-form]');

    avatarInput?.addEventListener('change', () => {
        if (avatarInput.files?.length) {
            avatarForm?.requestSubmit();
        }
    });
}

function initNotificationsToggle(root) {
    const button = root.querySelector('[data-messages-notifications-toggle]');
    const csrf = root.dataset.csrf;

    if (! button || ! csrf) {
        return;
    }

    button.addEventListener('click', async () => {
        const url = button.dataset.url;

        if (! url) {
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            const muted = Boolean(data.notifications_muted);

            button.dataset.muted = muted ? 'true' : 'false';

            const icon = button.querySelector('[data-messages-notifications-icon]');

            if (icon) {
                icon.classList.toggle('fa-bell', ! muted);
                icon.classList.toggle('fa-bell-slash', muted);
            }

            button.setAttribute('aria-label', muted ? 'Turn on notifications' : 'Turn off notifications');
            button.setAttribute('title', muted ? 'Notifications off' : 'Notifications on');
        } finally {
            button.disabled = false;
        }
    });
}

const root = document.querySelector('[data-messages]');

if (root) {
    initMessageComposer(root);
    initUserSearch(root);
    initGroupAvatar(root);
    initNotificationsToggle(root);
}
