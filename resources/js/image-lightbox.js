const lightbox = document.getElementById('image-lightbox');
const lightboxImage = lightbox?.querySelector('[data-image-lightbox-target]');
const closeButton = lightbox?.querySelector('[data-image-lightbox-close]');

function openImageLightbox(src, alt = 'Comment image') {
    if (! lightbox || ! lightboxImage) {
        return;
    }

    lightboxImage.src = src;
    lightboxImage.alt = alt;
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox() {
    if (! lightbox || ! lightboxImage) {
        return;
    }

    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
    lightboxImage.src = '';
    document.body.style.overflow = '';
}

function bindImageLightboxTriggers(root = document) {
    root.querySelectorAll('[data-image-lightbox]').forEach((trigger) => {
        if (trigger.dataset.lightboxBound === 'true') {
            return;
        }

        trigger.dataset.lightboxBound = 'true';

        trigger.addEventListener('click', () => {
            const src = trigger.getAttribute('data-image-lightbox');

            if (! src) {
                return;
            }

            openImageLightbox(src, trigger.getAttribute('data-image-lightbox-alt') ?? 'Comment image');
        });
    });
}

closeButton?.addEventListener('click', closeImageLightbox);

lightbox?.addEventListener('click', (event) => {
    if (event.target === lightbox) {
        closeImageLightbox();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && lightbox && ! lightbox.classList.contains('hidden')) {
        closeImageLightbox();
    }
});

bindImageLightboxTriggers();

export { bindImageLightboxTriggers, closeImageLightbox, openImageLightbox };
