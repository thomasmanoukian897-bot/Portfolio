function hasCommentContent(textarea, imageInput) {
    const hasText = textarea?.value.trim() !== '';
    const hasImage = imageInput?.files?.length > 0;

    return hasText || hasImage;
}

function bindCommentImageInput(form) {
    const textarea = form.querySelector('[data-comment-input], [data-comment-reply-input]');
    const imageInput = form.querySelector('[data-comment-image-input]');
    const submit = form.querySelector('[data-comment-submit], [data-comment-reply-submit]');
    const preview = form.querySelector('[data-comment-image-preview]');
    const previewImage = preview?.querySelector('img');
    const removeButton = form.querySelector('[data-comment-image-remove]');

    if (! imageInput || ! submit) {
        return;
    }

    const updateSubmitState = () => {
        submit.disabled = ! hasCommentContent(textarea, imageInput);
    };

    textarea?.addEventListener('input', updateSubmitState);

    imageInput.addEventListener('change', () => {
        const file = imageInput.files?.[0];

        if (! file) {
            if (preview) {
                preview.classList.add('hidden');
            }

            if (previewImage) {
                previewImage.src = '';
            }

            updateSubmitState();

            return;
        }

        if (preview && previewImage) {
            previewImage.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        }

        updateSubmitState();
    });

    removeButton?.addEventListener('click', () => {
        imageInput.value = '';

        if (preview) {
            preview.classList.add('hidden');
        }

        if (previewImage) {
            previewImage.src = '';
        }

        updateSubmitState();
    });

    updateSubmitState();
}

import { commentEmojiSectionHtml } from './comment-emojis';

function commentImageFieldHtml() {
    return `
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900 transition-colors">
                <i class="fa-regular fa-image"></i>
                <span>Add image</span>
                <input
                    type="file"
                    name="image"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    data-comment-image-input
                    class="sr-only"
                >
            </label>
            ${commentEmojiSectionHtml()}
            <div data-comment-image-preview class="hidden relative">
                <img src="" alt="Selected image preview" class="h-16 w-16 rounded-lg border border-slate-200 object-cover">
                <button
                    type="button"
                    data-comment-image-remove
                    aria-label="Remove selected image"
                    class="absolute -right-2 -top-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-700 transition-colors"
                >
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>
    `;
}

export { bindCommentImageInput, commentImageFieldHtml, hasCommentContent };
