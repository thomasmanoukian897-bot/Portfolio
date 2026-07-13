<div
    id="image-lightbox"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4"
    role="dialog"
    aria-modal="true"
    aria-label="Image preview"
>
    <button
        type="button"
        data-image-lightbox-close
        aria-label="Close image preview"
        class="absolute top-4 right-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors"
    >
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>

    <img
        data-image-lightbox-target
        src=""
        alt=""
        class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl"
    />
</div>
