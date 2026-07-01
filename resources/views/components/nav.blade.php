<nav class="sticky top-0 w-full z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200 shadow-sm">
    <div class="flex justify-between items-center px-6 md:px-16 py-4 max-w-7xl mx-auto">

        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                </svg>
            </div>
            <span class="text-xl font-bold text-slate-900 tracking-tight font-display">
                Digital Builder
            </span>
        </div>

        <div class="hidden md:flex items-center gap-8">
            <a class="text-sm font-semibold text-primary border-b-2 border-primary pb-1 transition-all" href="#">Home</a>
            <a class="text-sm font-semibold text-on-surface-variant hover:text-slate-900 transition-colors duration-200" href="#">Services</a>
            <a class="text-sm font-semibold text-on-surface-variant hover:text-slate-900 transition-colors duration-200" href="#">Portfolio</a>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                data-exporter-open
                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all hover:scale-[1.02] active:scale-[0.98]"
                title="Export Laravel Blade & Tailwind Files"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 6-6-6-6M12 19h8" />
                </svg>
                <span class="hidden sm:inline">Laravel Exporter</span>
                <span class="sm:hidden">Blade</span>
            </button>

            <button type="button" class="active:scale-95 transition-transform btn-gradient text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider">
                Start a Project
            </button>
        </div>
    </div>
</nav>
