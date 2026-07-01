<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Digital Builder | We Build the Future of the Web')</title>

    <!-- Tailwind CSS (Vite / Mix Integration) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Custom High Density Theme Helper Styles -->
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
        }

        /* High Density Slate Card */
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .glow-cyan {
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1), 0 2px 4px -2px rgba(59, 130, 246, 0.1);
        }

        .glow-purple {
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05);
        }

        /* Modern Slate Button */
        .btn-gradient {
            background: #0f172a;
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .btn-gradient:hover {
            background: #1e293b;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
            transform: translateY(-1px);
        }

        .animated-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(148, 163, 184, 0.15) 1px, transparent 0);
            background-size: 32px 32px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .mesh-gradient {
            background: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.03) 0%, transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(15, 23, 42, 0.02) 0%, transparent 50%);
        }
    </style>
    @stack('styles')
</head>
<body class="selection:bg-blue-600 selection:text-white">

    <!-- Top Navigation Bar Component -->
    @include('components.nav')

    <!-- Content slot yields here -->
    <main class="relative min-h-screen">
        @yield('content')
    </main>

    <!-- Footer Component -->
    @include('components.footer')

    @stack('scripts')
</body>
</html>