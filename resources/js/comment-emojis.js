const EMOJI_CATEGORIES = [
    {
        id: 'smileys',
        label: 'Smileys',
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '🙂', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😋', '😛', '😜', '🤪', '🤔', '😐', '😑', '😶', '🙄', '😏', '😣', '😥', '😮', '🤐', '😯', '😪', '😫', '🥱', '😴', '😌', '😔', '😢', '😭', '😤', '😠', '😡', '🤯', '😳', '🥺', '😎', '🤓', '🧐'],
    },
    {
        id: 'gestures',
        label: 'Gestures',
        emojis: ['👍', '👎', '👏', '🙌', '🤝', '👋', '🤞', '✌️', '🤟', '🤘', '👌', '🤌', '🤏', '👈', '👉', '👆', '👇', '☝️', '✋', '🤚', '🖐️', '🖖', '💪', '🙏', '✍️', '💅', '🤳', '💁', '🙋', '🙇', '🤦', '🤷'],
    },
    {
        id: 'hearts',
        label: 'Hearts',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '♥️'],
    },
    {
        id: 'objects',
        label: 'Objects',
        emojis: ['🔥', '✨', '💯', '🎉', '🎊', '⭐', '🌟', '💫', '⚡', '💥', '💢', '💤', '💬', '👀', '🎈', '🎁', '🏆', '🥇', '🎯', '✅', '❌', '❓', '❗', '💡', '🔔', '📌', '📎', '🔗'],
    },
];

let activePickerRoot = null;

function emojiCategoryTabsHtml() {
    return EMOJI_CATEGORIES.map((category, index) => `
        <button
            type="button"
            data-comment-emoji-category="${category.id}"
            aria-label="${category.label}"
            class="rounded-md px-2 py-1 text-xs font-semibold transition-colors ${index === 0 ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}"
        >
            ${category.label}
        </button>
    `).join('');
}

function emojiGridHtml(categoryId) {
    const category = EMOJI_CATEGORIES.find((item) => item.id === categoryId) ?? EMOJI_CATEGORIES[0];

    return category.emojis.map((emoji) => `
        <button
            type="button"
            data-comment-emoji="${emoji}"
            aria-label="Insert ${emoji}"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-xl transition-colors hover:bg-slate-100"
        >
            ${emoji}
        </button>
    `).join('');
}

function commentEmojiSectionHtml() {
    const firstCategory = EMOJI_CATEGORIES[0].id;

    return `
        <div class="relative" data-comment-emoji-root>
            <button
                type="button"
                data-comment-emoji-toggle
                aria-label="Add emoji"
                aria-expanded="false"
                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900 transition-colors"
            >
                <i class="fa-regular fa-face-smile"></i>
                <span>Emoji</span>
            </button>
            <div
                data-comment-emoji-picker
                class="absolute left-0 top-full z-20 mt-2 hidden w-72 rounded-xl border border-slate-200 bg-white p-3 shadow-lg"
            >
                <div class="mb-3 flex flex-wrap gap-1" data-comment-emoji-categories>
                    ${emojiCategoryTabsHtml()}
                </div>
                <div class="grid max-h-44 grid-cols-8 gap-0.5 overflow-y-auto" data-comment-emoji-grid data-active-category="${firstCategory}">
                    ${emojiGridHtml(firstCategory)}
                </div>
            </div>
        </div>
    `;
}

function insertEmojiAtCursor(textarea, emoji) {
    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? textarea.value.length;
    const text = textarea.value;

    textarea.value = `${text.slice(0, start)}${emoji}${text.slice(end)}`;

    const cursor = start + emoji.length;
    textarea.setSelectionRange(cursor, cursor);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
}

function closeEmojiPicker(root) {
    const picker = root?.querySelector('[data-comment-emoji-picker]');
    const toggle = root?.querySelector('[data-comment-emoji-toggle]');

    picker?.classList.add('hidden');
    toggle?.setAttribute('aria-expanded', 'false');

    if (activePickerRoot === root) {
        activePickerRoot = null;
    }
}

function openEmojiPicker(root) {
    if (activePickerRoot && activePickerRoot !== root) {
        closeEmojiPicker(activePickerRoot);
    }

    const picker = root.querySelector('[data-comment-emoji-picker]');
    const toggle = root.querySelector('[data-comment-emoji-toggle]');

    picker?.classList.remove('hidden');
    toggle?.setAttribute('aria-expanded', 'true');
    activePickerRoot = root;
}

function setActiveEmojiCategory(root, categoryId) {
    const categories = root.querySelector('[data-comment-emoji-categories]');
    const grid = root.querySelector('[data-comment-emoji-grid]');

    if (! categories || ! grid) {
        return;
    }

    categories.querySelectorAll('[data-comment-emoji-category]').forEach((button) => {
        const isActive = button.getAttribute('data-comment-emoji-category') === categoryId;

        button.classList.toggle('bg-slate-900', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('text-slate-600', ! isActive);
        button.classList.toggle('hover:bg-slate-100', ! isActive);
    });

    grid.dataset.activeCategory = categoryId;
    grid.innerHTML = emojiGridHtml(categoryId);
}

function bindCommentEmojiPicker(form) {
    const root = form.querySelector('[data-comment-emoji-root]');
    const textarea = form.querySelector('[data-comment-input], [data-comment-reply-input]');

    if (! root || ! textarea) {
        return;
    }

    const toggle = root.querySelector('[data-comment-emoji-toggle]');
    const picker = root.querySelector('[data-comment-emoji-picker]');
    const categories = root.querySelector('[data-comment-emoji-categories]');

    toggle?.addEventListener('click', (event) => {
        event.stopPropagation();

        if (picker?.classList.contains('hidden')) {
            openEmojiPicker(root);
        } else {
            closeEmojiPicker(root);
        }
    });

    categories?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-comment-emoji-category]');

        if (! button) {
            return;
        }

        setActiveEmojiCategory(root, button.getAttribute('data-comment-emoji-category'));
    });

    picker?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-comment-emoji]');

        if (! button) {
            return;
        }

        insertEmojiAtCursor(textarea, button.getAttribute('data-comment-emoji'));
        closeEmojiPicker(root);
    });
}

document.addEventListener('click', (event) => {
    if (! activePickerRoot) {
        return;
    }

    if (activePickerRoot.contains(event.target)) {
        return;
    }

    closeEmojiPicker(activePickerRoot);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activePickerRoot) {
        closeEmojiPicker(activePickerRoot);
    }
});

export { bindCommentEmojiPicker, commentEmojiSectionHtml, insertEmojiAtCursor };
