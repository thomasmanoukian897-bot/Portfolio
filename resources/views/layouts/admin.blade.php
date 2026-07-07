<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin | Digital Builder')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 font-sans">

    <div class="flex min-h-screen">
        <aside class="hidden lg:flex w-64 flex-col bg-slate-900 text-white">
            <div class="px-6 py-6 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-600/20 flex items-center justify-center border border-blue-500/30">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold font-display tracking-tight">Admin Panel</p>
                        <p class="text-[10px] text-slate-400 font-mono uppercase tracking-widest">Digital Builder</p>
                    </div>
                </a>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <a
                    href="{{ route('admin.dashboard') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors',
                        'bg-blue-600 text-white' => request()->routeIs('admin.dashboard'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.dashboard'),
                    ])
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    Dashboard
                </a>

                <a
                    href="{{ route('admin.users.index') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors',
                        'bg-blue-600 text-white' => request()->routeIs('admin.users.*'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.users.*'),
                    ])
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Users
                </a>

                <a
                    href="{{ route('admin.categories.index') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors',
                        'bg-blue-600 text-white' => request()->routeIs('admin.categories.*'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.categories.*'),
                    ])
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    Categories
                </a>

                <a
                    href="{{ route('admin.posts.index') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors',
                        'bg-blue-600 text-white' => request()->routeIs('admin.posts.*'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.posts.*'),
                    ])
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Posts
                </a>

                <a
                    href="{{ route('admin.bookings.index') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors',
                        'bg-blue-600 text-white' => request()->routeIs('admin.bookings.*'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.bookings.*'),
                    ])
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    Bookings
                </a>
            </nav>

            <div class="px-4 py-6 border-t border-slate-800 space-y-2">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    <i class="fa-solid fa-house w-4 text-center" aria-hidden="true"></i>
                    Home
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-300 hover:bg-red-600/20 hover:text-red-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between gap-4">
                <div class="lg:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-slate-900">Admin Panel</a>
                </div>

                <div class="hidden lg:block">
                    <h1 class="text-lg font-bold text-slate-900 font-display">@yield('heading', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                        aria-label="Home"
                    >
                        <i class="fa-solid fa-house" aria-hidden="true"></i>
                    </a>
                    <span class="text-sm text-slate-600 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono bg-blue-100 text-blue-700">
                        Admin
                    </span>
                </div>
            </header>

            <main class="flex-1 p-6">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
