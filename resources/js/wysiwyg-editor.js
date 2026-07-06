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

document.querySelectorAll('[data-wysiwyg-editor]').forEach((wrapper) => {
    const textarea = wrapper.querySelector('textarea');
    const editorEl = wrapper.querySelector('[data-wysiwyg-target]');

    if (! textarea || ! editorEl) {
        return;
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

    if (textarea.value) {
        quill.root.innerHTML = textarea.value;
    }

    quill.on('text-change', () => {
        syncEditorContent(quill, textarea);
    });

    const form = wrapper.closest('form');

    form?.addEventListener('submit', () => {
        syncEditorContent(quill, textarea);
    });
});
