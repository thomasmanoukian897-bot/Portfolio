import './bootstrap';
import './theme';
import './portfolio';
import './profile-dropdown';
import './mobile-drawer';
import './library-dropdown';
import './messages';
import './user-connections-modal';
import './image-lightbox';
import { bindCommentImageInput, commentImageFieldHtml, hasCommentContent } from './comment-images';
import { bindCommentEmojiPicker } from './comment-emojis';
import './wysiwyg-editor';
import './posts-view-toggle';
import { initMentionAutocomplete, initMentionInputs } from './mention-autocomplete';

const templates = JSON.parse(document.getElementById('exporter-templates-data')?.textContent ?? '[]');

const modal = document.getElementById('exporter-modal');
const templateSelect = document.getElementById('exporter-template-select');
const filenameEl = document.getElementById('exporter-filename');
const descriptionEl = document.getElementById('exporter-description');
const codeEl = document.getElementById('exporter-code');
const copyLabel = document.getElementById('exporter-copy-label');
const panelCode = document.getElementById('exporter-panel-code');
const panelGuide = document.getElementById('exporter-panel-guide');
const fileSelect = document.getElementById('exporter-file-select');

let selectedId = templates[0]?.id ?? 'layout';
let activeTab = 'code';

function getTemplate(id) {
    return templates.find((template) => template.id === id) ?? templates[0];
}

function setSelectedTemplate(id) {
    selectedId = id;
    const template = getTemplate(id);

    if (! template) {
        return;
    }

    if (templateSelect) {
        templateSelect.value = id;
    }

    document.querySelectorAll('[data-exporter-template]').forEach((button) => {
        const isActive = button.getAttribute('data-exporter-template') === id;

        button.classList.toggle('bg-blue-50', isActive);
        button.classList.toggle('border-blue-200', isActive);
        button.classList.toggle('text-blue-700', isActive);
        button.classList.toggle('bg-white', ! isActive);
        button.classList.toggle('border-slate-200', ! isActive);
        button.classList.toggle('hover:bg-slate-100', ! isActive);
        button.classList.toggle('text-slate-600', ! isActive);
    });

    if (filenameEl) {
        filenameEl.textContent = template.filename;
    }

    if (descriptionEl) {
        descriptionEl.textContent = template.description;
    }

    if (codeEl) {
        codeEl.textContent = template.code;
    }
}

function setActiveTab(tab) {
    activeTab = tab;

    document.querySelectorAll('[data-exporter-tab]').forEach((button) => {
        const isActive = button.getAttribute('data-exporter-tab') === tab;

        button.classList.toggle('bg-slate-900', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('shadow-xs', isActive);
        button.classList.toggle('text-slate-600', ! isActive);
        button.classList.toggle('hover:text-slate-950', ! isActive);
    });

    panelCode?.classList.toggle('hidden', tab !== 'code');
    panelGuide?.classList.toggle('hidden', tab !== 'guide');
    fileSelect?.classList.toggle('hidden', tab !== 'code');
}

function openModal() {
    if (! modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    if (! modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function toggleModal() {
    if (modal?.classList.contains('hidden')) {
        openModal();
    } else {
        closeModal();
    }
}

async function copyCode() {
    const template = getTemplate(selectedId);

    if (! template?.code) {
        return;
    }

    await navigator.clipboard.writeText(template.code);

    if (copyLabel) {
        copyLabel.textContent = 'Copied!';
        setTimeout(() => {
            copyLabel.textContent = 'Copy Code';
        }, 2000);
    }
}

function downloadCode() {
    const template = getTemplate(selectedId);

    if (! template?.code) {
        return;
    }

    const parts = template.filename.split('/');
    const filename = parts[parts.length - 1];
    const blob = new Blob([template.code], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

async function copyText(text) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // Fall through to legacy copy method.
        }
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    textarea.setSelectionRange(0, text.length);

    let copied = false;

    try {
        copied = document.execCommand('copy');
    } catch {
        copied = false;
    }

    document.body.removeChild(textarea);

    return copied;
}

function setVoteIconState(icon, type, isActive) {
    const isUp = type === 'up';

    icon.classList.remove('fa-regular', 'fa-solid', 'fa-thumbs-up', 'fa-thumbs-down');
    icon.classList.add(isActive ? 'fa-solid' : 'fa-regular');
    icon.classList.add(isUp ? 'fa-thumbs-up' : 'fa-thumbs-down');
    icon.style.color = isActive
        ? (isUp ? 'rgb(255, 212, 59)' : 'rgb(255, 0, 0)')
        : '';
}

function setReplySubmitState(textarea, imageInput) {
    const form = textarea?.closest('[data-comment-reply-form]') ?? imageInput?.closest('[data-comment-reply-form]');
    const submit = form?.querySelector('[data-comment-reply-submit]');

    if (submit) {
        submit.disabled = ! hasCommentContent(textarea, imageInput);
    }
}

function closeAllReplyForms() {
    document.querySelectorAll('[data-comment-reply-slot]').forEach((slot) => {
        slot.innerHTML = '';
        slot.classList.add('hidden');
    });
}

function openReplyForm(button, initialBody = '') {
    const commentId = button.getAttribute('data-comment-reply');
    const authorName = button.getAttribute('data-comment-reply-author');
    const action = button.getAttribute('data-comment-reply-action');
    const token = button.getAttribute('data-comment-reply-csrf');
    const slot = document.querySelector(`[data-comment-reply-slot="${commentId}"]`);

    if (! commentId || ! authorName || ! action || ! token || ! slot) {
        return;
    }

    closeAllReplyForms();

    slot.classList.remove('hidden');
    slot.innerHTML = `
        <form method="POST" action="${action}" enctype="multipart/form-data" data-comment-reply-form="${commentId}">
            <input type="hidden" name="_token" value="${token}">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <label class="sr-only" for="reply-body-${commentId}">Reply to ${authorName}</label>
                <textarea
                    id="reply-body-${commentId}"
                    name="body"
                    rows="3"
                    placeholder="Replying to ${authorName}. Use @ to mention someone."
                    data-comment-reply-input
                    data-mention-input
                    class="w-full resize-none border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0"
                ></textarea>
                ${commentImageFieldHtml()}
                <div class="mt-3 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        data-comment-reply-cancel="${commentId}"
                        class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        data-comment-reply-submit
                        class="inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition-colors disabled:cursor-not-allowed disabled:bg-slate-300"
                        disabled
                    >
                        Respond
                    </button>
                </div>
            </div>
        </form>
    `;

    const textarea = slot.querySelector('[data-comment-reply-input]');
    const imageInput = slot.querySelector('[data-comment-image-input]');
    const mentionSearchUrl = commentsSection?.dataset.usersSearchUrl ?? null;

    if (textarea) {
        textarea.value = initialBody;
        textarea.addEventListener('input', () => {
            setReplySubmitState(textarea, imageInput);
        });
        initMentionAutocomplete(textarea, mentionSearchUrl);
        textarea.focus();
    }

    const replyForm = slot.querySelector('[data-comment-reply-form]');

    if (replyForm) {
        bindCommentImageInput(replyForm);
        bindCommentEmojiPicker(replyForm);
    }

    slot.querySelector('[data-comment-reply-cancel]')?.addEventListener('click', () => {
        closeAllReplyForms();
    });

    slot.querySelector('[data-comment-reply-form]')?.addEventListener('submit', (event) => {
        const submit = event.target.querySelector('[data-comment-reply-submit]');

        if (submit) {
            submit.disabled = true;
        }
    });
}

function repliesToggleLabel(count, expanded) {
    if (expanded) {
        return 'Hide replies';
    }

    return `${count} ${Number(count) === 1 ? 'reply' : 'replies'}`;
}

document.querySelectorAll('[data-comment-replies-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const commentId = button.getAttribute('data-comment-replies-toggle');
        const count = button.getAttribute('data-replies-count');
        const list = document.querySelector(`[data-comment-replies-list="${commentId}"]`);

        if (! commentId || ! list) {
            return;
        }

        const expanded = list.classList.toggle('hidden') === false;

        button.setAttribute('aria-expanded', String(expanded));
        button.textContent = repliesToggleLabel(count, expanded);
    });
});

document.querySelectorAll('[data-comment-reply]').forEach((button) => {
    button.addEventListener('click', () => {
        openReplyForm(button);
    });
});

const commentsSection = document.getElementById('comments');

if (commentsSection?.dataset.replyTo) {
    const replyButton = document.querySelector(`[data-comment-reply="${commentsSection.dataset.replyTo}"]`);

    if (replyButton) {
        openReplyForm(replyButton, commentsSection.dataset.replyBody ?? '');
    }
}

const composerForm = document.getElementById('comment-composer-form');

if (composerForm && ! composerForm.dataset.bound) {
    composerForm.dataset.bound = 'true';

    bindCommentImageInput(composerForm);
    bindCommentEmojiPicker(composerForm);

    composerForm.addEventListener('submit', (event) => {
        const submit = composerForm.querySelector('#comment-composer-submit');
        const textarea = composerForm.querySelector('[data-comment-input]');
        const imageInput = composerForm.querySelector('[data-comment-image-input]');

        if (submit?.disabled || ! hasCommentContent(textarea, imageInput)) {
            event.preventDefault();

            return;
        }

        if (submit) {
            submit.disabled = true;
        }

        closeAllReplyForms();
    });
}

document.querySelectorAll('[data-comment-vote]').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-comment-vote');
        const token = button.getAttribute('data-csrf');
        const voteType = button.getAttribute('data-vote-type');
        const group = button.closest('[data-comment-vote-group]');

        if (! url || ! token || ! voteType || ! group) {
            return;
        }

        const buttons = group.querySelectorAll('[data-comment-vote]');
        buttons.forEach((voteButton) => {
            voteButton.disabled = true;
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ type: voteType }),
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            const upCount = group.querySelector('[data-vote-count="up"]');
            const downCount = group.querySelector('[data-vote-count="down"]');

            if (upCount) {
                upCount.textContent = String(data.up_count);
            }

            if (downCount) {
                downCount.textContent = String(data.down_count);
            }

            buttons.forEach((voteButton) => {
                const type = voteButton.getAttribute('data-vote-type');
                const icon = voteButton.querySelector('[data-vote-icon]');

                if (! type || ! icon) {
                    return;
                }

                setVoteIconState(icon, type, data.vote === type);
            });
        } finally {
            buttons.forEach((voteButton) => {
                voteButton.disabled = false;
            });
        }
    });
});

document.querySelectorAll('[data-post-like]').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-post-like');
        const token = button.getAttribute('data-csrf');
        const icon = button.querySelector('[data-like-icon]');
        const countEl = button.querySelector('[data-like-count]');

        if (! url || ! token || ! icon) {
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (countEl) {
                countEl.textContent = String(data.count);
            }

            if (data.liked) {
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
                icon.style.color = 'rgb(255, 0, 0)';
            } else {
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
                icon.style.color = '';
            }
        } finally {
            button.disabled = false;
        }
    });
});

document.querySelectorAll('[data-post-bookmark]').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-post-bookmark');
        const token = button.getAttribute('data-csrf');
        const icon = button.querySelector('[data-bookmark-icon]');

        if (! url || ! token || ! icon) {
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (data.bookmarked) {
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
                icon.style.color = 'rgb(255, 212, 59)';
            } else {
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
                icon.style.color = '';
            }
        } finally {
            button.disabled = false;
        }
    });
});

document.querySelectorAll('[data-copy-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-copy-url');

        if (! url) {
            return;
        }

        const icon = button.querySelector('[data-copy-icon]');
        const label = button.querySelector('[data-copy-label]');
        const copied = await copyText(url);

        if (! copied) {
            return;
        }

        if (icon) {
            icon.classList.remove('fa-link');
            icon.classList.add('fa-check');

            setTimeout(() => {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-link');
            }, 2000);
        }

        if (label) {
            const original = label.textContent;

            label.textContent = 'Copied!';

            setTimeout(() => {
                label.textContent = original;
            }, 2000);
        }
    });
});

document.querySelectorAll('[data-exporter-open]').forEach((button) => {
    button.addEventListener('click', openModal);
});

document.querySelectorAll('[data-exporter-close]').forEach((element) => {
    element.addEventListener('click', closeModal);
});

document.querySelectorAll('[data-exporter-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        setActiveTab(button.getAttribute('data-exporter-tab') ?? 'code');
    });
});

document.querySelectorAll('[data-exporter-template]').forEach((button) => {
    button.addEventListener('click', () => {
        setSelectedTemplate(button.getAttribute('data-exporter-template') ?? selectedId);
    });
});

templateSelect?.addEventListener('change', (event) => {
    setSelectedTemplate(event.target.value);
});

document.getElementById('exporter-copy')?.addEventListener('click', copyCode);
document.getElementById('exporter-download')?.addEventListener('click', downloadCode);

document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        toggleModal();
    }

    if (event.key === 'Escape' && modal && ! modal.classList.contains('hidden')) {
        closeModal();
    }
});

setSelectedTemplate(selectedId);
setActiveTab(activeTab);

initMentionInputs(commentsSection?.dataset.usersSearchUrl ?? null);
