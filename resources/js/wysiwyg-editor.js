import Quill from 'quill';
import 'quill/dist/quill.snow.css';

function isEmptyContent(html) {
    const trimmed = html.trim();

    return trimmed === '' || trimmed === '<p><br></p>' || trimmed === '<p></p>';
}

function syncEditorContent(quill, textarea) {
    const html = quill.root.innerHTML;

    textarea.value = isEmptyContent(html) ? '' : html;
}

function clampTooltipPosition(wrapper, quillRoot) {
    requestAnimationFrame(() => {
        const tooltip = quillRoot?.querySelector('.ql-tooltip');

        if (! tooltip || tooltip.classList.contains('ql-hidden')) {
            return;
        }

        const padding = 8;
        const maxLeft = wrapper.clientWidth - tooltip.offsetWidth - padding;
        const currentLeft = parseFloat(tooltip.style.left) || 0;

        if (currentLeft < padding) {
            tooltip.style.left = `${padding}px`;
        } else if (currentLeft > maxLeft) {
            tooltip.style.left = `${Math.max(padding, maxLeft)}px`;
        }
    });
}

function watchTooltipPosition(wrapper, quillRoot, quill) {
    const observer = new MutationObserver(() => {
        clampTooltipPosition(wrapper, quillRoot);
    });

    observer.observe(quillRoot, {
        attributes: true,
        subtree: true,
        attributeFilter: ['class', 'style'],
    });

    quill.on('selection-change', () => {
        clampTooltipPosition(wrapper, quillRoot);
    });
}

function setMode(wrapper, quill, textarea, htmlSource, mode) {
    const modeButtons = wrapper.querySelectorAll('[data-wysiwyg-mode]');
    const currentMode = wrapper.dataset.wysiwygMode ?? 'visual';

    if (mode === currentMode) {
        return;
    }

    if (mode === 'html') {
        syncEditorContent(quill, textarea);
        htmlSource.value = textarea.value;
        quill.disable();
    } else {
        quill.root.innerHTML = htmlSource.value || '';
        syncEditorContent(quill, textarea);
        quill.enable();
    }

    wrapper.dataset.wysiwygMode = mode;

    modeButtons.forEach((button) => {
        const isActive = button.dataset.wysiwygMode === mode;

        button.classList.toggle('wysiwyg-mode-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
}

document.querySelectorAll('[data-wysiwyg-editor]').forEach((wrapper) => {
    const textarea = wrapper.querySelector('[data-wysiwyg-input]');
    const editorEl = wrapper.querySelector('[data-wysiwyg-target]');
    const htmlSource = wrapper.querySelector('[data-wysiwyg-html]');

    if (! textarea || ! editorEl || ! htmlSource) {
        return;
    }

    const initialContent = textarea.value;

    if (initialContent) {
        editorEl.innerHTML = initialContent;
        htmlSource.value = initialContent;
    }

    const quill = new Quill(editorEl, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean'],
            ],
        },
        placeholder: 'Write your post content...',
    });

    if (initialContent && isEmptyContent(quill.root.innerHTML)) {
        quill.clipboard.dangerouslyPasteHTML(initialContent);
        htmlSource.value = quill.root.innerHTML;
        syncEditorContent(quill, textarea);
    }

    wrapper.dataset.wysiwygMode = 'visual';

    const quillRoot = wrapper.querySelector('.ql-snow');

    if (quillRoot) {
        watchTooltipPosition(wrapper, quillRoot, quill);
    }

    wrapper.querySelectorAll('[data-wysiwyg-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            setMode(wrapper, quill, textarea, htmlSource, button.dataset.wysiwygMode ?? 'visual');
        });
    });

    quill.on('text-change', () => {
        if (wrapper.dataset.wysiwygMode === 'visual') {
            syncEditorContent(quill, textarea);
        }
    });

    htmlSource.addEventListener('input', () => {
        if (wrapper.dataset.wysiwygMode === 'html') {
            textarea.value = htmlSource.value;
        }
    });

    const form = wrapper.closest('form');

    form?.addEventListener('submit', () => {
        if (wrapper.dataset.wysiwygMode === 'html') {
            textarea.value = htmlSource.value;
        } else {
            syncEditorContent(quill, textarea);
        }
    });
});
