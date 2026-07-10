<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Digital Builder | We Build the Future of the Web')</title>

    <script>
        (function () {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (theme === 'dark' || (! theme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.webawesome.com/3.6.0/styles/webawesome.css" />
    <script type="module" src="https://cdn.webawesome.com/3.6.0/webawesome.loader.js"></script>
    <script type="module">
        import 'https://cdn.webawesome.com/3.6.0/components/icon/icon.js';
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen bg-background text-slate-900 dark:text-slate-100 font-sans selection:bg-blue-600 selection:text-white">

    @include('components.nav')

    <main class="relative min-h-screen">
        @yield('content')
    </main>

    @include('components.footer')

    @include('components.exporter-modal')

    @stack('scripts')
</body>
</html>
