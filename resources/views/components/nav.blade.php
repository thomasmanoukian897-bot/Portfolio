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

                <div class="relative" data-profile-dropdown>
                    <button
                        type="button"
                        data-profile-dropdown-toggle
                        aria-expanded="false"
                        aria-haspopup="true"
                        @class([
                            'flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold border transition-all hover:scale-[1.02] active:scale-[0.98]',
                            'bg-primary/10 text-primary border-primary/20' => request()->routeIs('profile.*'),
                            'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border-slate-300 dark:border-slate-600' => ! request()->routeIs('profile.*'),
                        ])
                    >
                        <x-user-avatar :user="auth()->user()" size="sm" />
                        <span class="hidden sm:inline">Profile</span>
                        <svg class="w-4 h-4 hidden sm:block opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        data-profile-dropdown-menu
                        class="hidden absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg py-2 z-50"
                    >
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-3">
                                <x-user-avatar :user="auth()->user()" />
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="py-1">
                            <a
                                href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                Edit Profile
                            </a>

                            @if (auth()->user()->isAdmin())
                                <a
                                    href="{{ route('admin.dashboard') }}"
                                    class="sm:hidden flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Admin Dashboard
                                </a>
                            @endif

                            <a
                                href="{{ route('posts.create') }}"
                                class="sm:hidden flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                <i class="fa-solid fa-pen w-4 text-center opacity-60"></i>
                                Write Post
                            </a>
                        </div>

                        <div class="border-t border-slate-200 dark:border-slate-700 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                >
                                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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
