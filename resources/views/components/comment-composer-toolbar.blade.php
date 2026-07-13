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
                <button
                    type="button"
                    data-comment-emoji-category="smileys"
                    aria-label="Smileys"
                    class="rounded-md bg-slate-900 px-2 py-1 text-xs font-semibold text-white transition-colors"
                >
                    Smileys
                </button>
                <button
                    type="button"
                    data-comment-emoji-category="gestures"
                    aria-label="Gestures"
                    class="rounded-md px-2 py-1 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                >
                    Gestures
                </button>
                <button
                    type="button"
                    data-comment-emoji-category="hearts"
                    aria-label="Hearts"
                    class="rounded-md px-2 py-1 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                >
                    Hearts
                </button>
                <button
                    type="button"
                    data-comment-emoji-category="objects"
                    aria-label="Objects"
                    class="rounded-md px-2 py-1 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                >
                    Objects
                </button>
            </div>

            <div class="grid max-h-44 grid-cols-8 gap-0.5 overflow-y-auto" data-comment-emoji-grid data-active-category="smileys">
                @foreach (['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '🙂', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😋', '😛', '😜', '🤪', '🤔', '😐', '😑', '😶', '🙄', '😏', '😣', '😥', '😮', '🤐', '😯', '😪', '😫', '🥱', '😴', '😌', '😔', '😢', '😭', '😤', '😠', '😡', '🤯', '😳', '🥺', '😎', '🤓', '🧐'] as $emoji)
                    <button
                        type="button"
                        data-comment-emoji="{{ $emoji }}"
                        aria-label="Insert {{ $emoji }}"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-xl transition-colors hover:bg-slate-100"
                    >
                        {{ $emoji }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

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
