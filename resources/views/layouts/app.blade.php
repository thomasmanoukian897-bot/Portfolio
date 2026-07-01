<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Digital Builder | We Build the Future of the Web')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen bg-background text-slate-900 font-sans selection:bg-blue-600 selection:text-white">

    @include('components.nav')

    <main class="relative min-h-screen">
        @yield('content')
    </main>

    @include('components.footer')

    @include('components.exporter-modal')

    <div class="fixed bottom-6 right-6 z-30">
        <button
            type="button"
            data-exporter-open
            class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900/95 border border-slate-700 text-white hover:bg-slate-800 transition-all duration-300 hover:scale-[1.05] active:scale-[0.95] shadow-lg shadow-black/20 backdrop-blur-md"
        >
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 6-6-6-6M12 19h8" />
            </svg>
            <span class="text-xs font-bold font-mono tracking-wider">Laravel Blade Code</span>
            <div class="hidden sm:flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-[9px] font-mono text-slate-300 font-bold">
                Ctrl + K
            </div>
        </button>
    </div>

    @stack('scripts')
</body>
</html>
