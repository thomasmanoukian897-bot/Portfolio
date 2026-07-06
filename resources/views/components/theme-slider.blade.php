<div class="flex items-center justify-between gap-4 px-4 py-3">
    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Theme</span>
    <div class="flex items-center gap-2.5">
        <i class="fa-solid fa-sun text-xs text-amber-500" aria-hidden="true"></i>
        <label class="relative inline-flex cursor-pointer items-center">
            <input
                type="checkbox"
                data-theme-slider
                role="switch"
                aria-label="Toggle dark mode"
                class="peer sr-only"
            >
            <span class="relative h-6 w-11 rounded-full bg-slate-200 transition-colors after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform after:content-[''] peer-checked:bg-primary peer-checked:after:translate-x-full peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary dark:bg-slate-600 rtl:peer-checked:after:-translate-x-full"></span>
        </label>
        <i class="fa-solid fa-moon text-xs text-indigo-400" aria-hidden="true"></i>
    </div>
</div>
