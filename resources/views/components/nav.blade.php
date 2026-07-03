<nav class="sticky top-0 w-full z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="flex justify-between items-center px-6 md:px-16 py-4 max-w-7xl mx-auto">

        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                </svg>
            </div>
            <span class="text-xl font-bold text-slate-900 dark:text-slate-100 tracking-tight font-display">
                Digital Builder
            </span>
        </a>

        <div class="hidden md:flex items-center gap-8">
            <a @class([
                'text-sm font-semibold transition-all',
                'text-primary border-b-2 border-primary pb-1' => request()->routeIs('home'),
                'text-on-surface-variant hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-200' => ! request()->routeIs('home'),
            ]) href="{{ route('home') }}">Home</a>
            <a @class([
                'text-sm font-semibold transition-all',
                'text-primary border-b-2 border-primary pb-1' => request()->routeIs('services'),
                'text-on-surface-variant hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-200' => ! request()->routeIs('services'),
            ]) href="{{ route('services') }}">Services</a>
            <a @class([
                'text-sm font-semibold transition-all',
                'text-primary border-b-2 border-primary pb-1' => request()->routeIs('portfolio'),
                'text-on-surface-variant hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-200' => ! request()->routeIs('portfolio'),
            ]) href="{{ route('portfolio') }}">Portfolio</a>
            <a @class([
                'text-sm font-semibold transition-all',
                'text-primary border-b-2 border-primary pb-1' => request()->routeIs('posts.*'),
                'text-on-surface-variant hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-200' => ! request()->routeIs('posts.*'),
            ]) href="{{ route('posts.index') }}">Blog</a>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                data-theme-toggle
                aria-label="Switch to dark mode"
                class="flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
            >
                <svg data-theme-icon="dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
                <svg data-theme-icon="light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </button>

            <a
                href="{{ route('contact') }}"
                @class([
                    'flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold border transition-all hover:scale-[1.02] active:scale-[0.98]',
                    'bg-primary/10 text-primary border-primary/20' => request()->routeIs('contact'),
                    'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border-slate-300 dark:border-slate-600' => ! request()->routeIs('contact'),
                ])
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <span class="hidden sm:inline">Contact</span>
            </a>

            @auth
                @if (auth()->user()->isAdmin())
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="hidden sm:inline-flex active:scale-95 transition-transform px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-200"
                    >
                        Admin
                    </a>
                @endif

                <a
                    href="{{ route('posts.create') }}"
                    class="hidden sm:inline-flex items-center gap-2 active:scale-95 transition-transform px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20 hover:bg-primary/20"
                >
                    <i class="fa-solid fa-pen"></i>
                    Write Post
                </a>

                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button
                        type="submit"
                        class="active:scale-95 transition-transform btn-gradient text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider"
                    >
                        Sign Out
                    </button>
                </form>
            @else
                <a
                    href="{{ route('register') }}"
                    @class([
                        'hidden sm:inline-flex active:scale-95 transition-transform px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider',
                        'btn-gradient text-white' => ! request()->routeIs('register'),
                        'bg-primary/10 text-primary border border-primary/20' => request()->routeIs('register'),
                    ])
                >
                    Register
                </a>
                <a
                    href="{{ route('login') }}"
                    @class([
                        'active:scale-95 transition-transform px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider',
                        'btn-gradient text-white' => ! request()->routeIs('login') && ! request()->routeIs('register'),
                        'bg-primary/10 text-primary border border-primary/20' => request()->routeIs('login'),
                        'glass-card text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600' => request()->routeIs('register'),
                    ])
                >
                    Sign In
                </a>
            @endauth
        </div>
    </div>
</nav>
