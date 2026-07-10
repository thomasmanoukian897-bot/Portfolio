@props([
    'profileUser',
])

<div
    id="user-connections-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="user-connections-modal-title"
    data-followers-url="{{ route('users.followers', $profileUser) }}"
    data-following-url="{{ route('users.following', $profileUser) }}"
>
    <div class="absolute inset-0" data-user-connections-close></div>

    <div class="relative z-10 flex w-full max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900 max-h-[min(70vh,32rem)]">
        <div class="relative flex items-center justify-center border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <h2 id="user-connections-modal-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">
                Followers
            </h2>

            <button
                type="button"
                data-user-connections-close
                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                aria-label="Close"
            >
                <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <label class="sr-only" for="user-connections-search">Search users</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                <input
                    id="user-connections-search"
                    type="search"
                    placeholder="Search"
                    autocomplete="off"
                    class="w-full rounded-xl border-0 bg-slate-100 py-2.5 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500"
                />
            </div>
        </div>

        <div
            id="user-connections-list"
            class="flex-1 overflow-y-auto"
            role="list"
        ></div>
    </div>
</div>
