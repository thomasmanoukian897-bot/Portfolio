export interface LaravelTemplate {
  id: string;
  name: string;
  filename: string;
  description: string;
  language: "blade" | "php" | "javascript" | "css";
  code: string;
}

export const laravelTemplates: LaravelTemplate[] = [
  {
    id: "layout",
    name: "Master Layout",
    filename: "resources/views/layouts/app.blade.php",
    description: "The primary HTML5 shell for your Laravel application. Loads Tailwind CSS, Google Fonts, and outlines the layout with dynamic content slots.",
    language: "blade",
    code: `<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

    <!-- Custom High-Tech Helper Styles -->
    <style>
        body {
            background-color: #131313;
            color: #e5e2e1;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
        }

        .glass-card {
            background: rgba(32, 31, 31, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            pointer-events: none;
        }

        .glow-cyan {
            box-shadow: 0 0 30px rgba(0, 240, 255, 0.2);
        }

        .glow-purple {
            box-shadow: 0 0 30px rgba(207, 92, 255, 0.2);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #00f0ff 0%, #cf5cff 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.5);
            transform: translateY(-2px);
        }

        .animated-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #7df4ff 0%, #ecb2ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .mesh-gradient {
            background: radial-gradient(circle at 0% 0%, rgba(0, 240, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(207, 92, 255, 0.1) 0%, transparent 50%);
        }
    </style>
    @stack('styles')
</head>
<body class="selection:bg-primary-container selection:text-on-primary-fixed">

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
</html>`
  },
  {
    id: "index",
    name: "Landing Page View",
    filename: "resources/views/index.blade.php",
    description: "The primary page template. Combines and sequences all sections including Hero, Stats, Value Proposition, and Call To Action components.",
    language: "blade",
    code: `@extends('layouts.app')

@section('title', 'Digital Builder | We Build the Future of the Web')

@section('content')
    <!-- Hero Component -->
    @include('components.hero')

    <!-- Stats Component -->
    @include('components.stats')

    <!-- Bento Grid Performance Component -->
    @include('components.performance')

    <!-- CTA Section Component -->
    @include('components.cta')
@endsection`
  },
  {
    id: "nav",
    name: "Navbar Component",
    filename: "resources/views/components/nav.blade.php",
    description: "The polished global navigation header featuring dynamic blur effects, menu items, and the call-to-action button.",
    language: "blade",
    code: `<nav class="sticky top-0 w-full z-50 bg-[#131313]/80 backdrop-blur-xl border-b border-white/10 shadow-[0_0_15px_rgba(0,240,255,0.1)]">
    <div class="flex justify-between items-center px-4 md:px-16 py-4 max-w-7xl mx-auto">
        
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <svg class="w-10 h-10 text-[#00f0ff]" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="25" y="25" width="50" height="50" rx="12" stroke="currentColor" stroke-width="8" stroke-dasharray="4 4" />
                <path d="M50 20 L80 50 L50 80 L20 50 Z" stroke="#cf5cff" stroke-width="6" />
                <circle cx="50" cy="50" r="10" fill="#00f0ff" />
            </svg>
            <span class="text-xl font-bold text-[#00f0ff] tracking-tight" style="font-family: 'Space Grotesk', sans-serif;">
                Digital Builder
            </span>
        </div>

        <!-- Links -->
        <div class="hidden md:flex items-center gap-8">
            <a class="text-sm font-semibold text-[#00dbe9] border-b-2 border-[#00dbe9] pb-1 transition-all" href="#">Home</a>
            <a class="text-sm font-semibold text-[#b9cacb] hover:text-[#7df4ff] transition-colors duration-200" href="#">Services</a>
            <a class="text-sm font-semibold text-[#b9cacb] hover:text-[#7df4ff] transition-colors duration-200" href="#">Portfolio</a>
        </div>

        <!-- CTA Button -->
        <button class="active:scale-95 transition-transform btn-gradient text-[#002022] px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider">
            Start a Project
        </button>
    </div>
</nav>`
  },
  {
    id: "hero",
    name: "Hero Section",
    filename: "resources/views/components/hero.blade.php",
    description: "The hero banner segment displaying high-tech mesh animations, action buttons, and a decorative floating code element.",
    language: "blade",
    code: `<section class="relative min-h-[90vh] flex flex-col items-center justify-center pt-16 overflow-hidden mesh-gradient">
    <div class="absolute inset-0 animated-grid opacity-40 pointer-events-none"></div>
    
    <div class="relative z-10 text-center px-6 max-w-5xl mx-auto space-y-8">
        <!-- Next-Gen Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 backdrop-blur-md">
            <span class="w-2 h-2 rounded-full bg-[#00f0ff] animate-pulse"></span>
            <span class="text-xs text-[#00dbe9] uppercase tracking-widest" style="font-family: monospace;">Next-Gen Web Infrastructure</span>
        </div>

        <!-- Heading -->
        <h1 class="text-[42px] md:text-7xl font-bold tracking-tighter leading-tight text-white" style="font-family: 'Space Grotesk', sans-serif;">
            We Build the <span class="gradient-text">Future of the Web</span>
        </h1>

        <!-- Subheading -->
        <p class="text-lg md:text-xl text-[#b9cacb] max-w-2xl mx-auto leading-relaxed">
            Engineering production-ready digital products with relentless velocity. We empower startups to scale from idea to global infrastructure in record time.
        </p>

        <!-- CTA Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <button class="w-full sm:w-auto px-10 py-4 btn-gradient text-[#002022] rounded-xl text-sm font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                Build with us
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
            <button class="w-full sm:w-auto px-10 py-4 glass-card hover:bg-[#2a2a2a] transition-colors rounded-xl text-sm font-bold uppercase tracking-widest text-[#e5e2e1]">
                View Ecosystem
            </button>
        </div>
    </div>

    <!-- Floating Code Element -->
    <div class="hidden lg:block absolute bottom-12 right-16 glass-card p-6 rounded-xl w-80 animate-bounce duration-[5000ms]">
        <div class="flex gap-1.5 mb-4">
            <div class="w-3 h-3 rounded-full bg-[#ffb4ab]"></div>
            <div class="w-3 h-3 rounded-full bg-[#33fb0a]"></div>
            <div class="w-3 h-3 rounded-full bg-[#00f0ff]"></div>
        </div>
        <pre class="text-xs text-[#b9cacb] leading-relaxed" style="font-family: monospace;"><span class="text-[#ecb2ff]">const</span> <span class="text-[#00f0ff]">builder</span> = <span class="text-[#ecb2ff]">new</span> DigitalBuilder();
builder.<span class="text-[#79ff5b]">ship</span>({
  velocity: <span class="text-[#ecb2ff]">"maximum"</span>,
  quality: <span class="text-[#ecb2ff]">"enterprise"</span>,
  scale: <span class="text-[#ecb2ff]">"infinite"</span>
});</pre>
    </div>
</section>`
  },
  {
    id: "stats",
    name: "Stats Grid",
    filename: "resources/views/components/stats.blade.php",
    description: "The metric container grid listing active stats with border-glow interactions on hover.",
    language: "blade",
    code: `<section class="py-20 px-6 max-w-7xl mx-auto">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        
        <!-- Stat Card 1 -->
        <div class="glass-card p-8 rounded-2xl text-center group transition-all hover:border-[#00f0ff]/40">
            <div class="text-4xl md:text-5xl font-bold gradient-text mb-1" style="font-family: 'Space Grotesk', sans-serif;">120+</div>
            <div class="text-xs text-[#b9cacb] uppercase tracking-widest">Projects Shipped</div>
        </div>

        <!-- Stat Card 2 -->
        <div class="glass-card p-8 rounded-2xl text-center group transition-all hover:border-[#cf5cff]/40">
            <div class="text-4xl md:text-5xl font-bold gradient-text mb-1" style="font-family: 'Space Grotesk', sans-serif;">99.9%</div>
            <div class="text-xs text-[#b9cacb] uppercase tracking-widest">Uptime Record</div>
        </div>

        <!-- Stat Card 3 -->
        <div class="glass-card p-8 rounded-2xl text-center group transition-all hover:border-[#00f0ff]/40">
            <div class="text-4xl md:text-5xl font-bold gradient-text mb-1" style="font-family: 'Space Grotesk', sans-serif;">8yr+</div>
            <div class="text-xs text-[#b9cacb] uppercase tracking-widest">Expertise</div>
        </div>

        <!-- Stat Card 4 -->
        <div class="glass-card p-8 rounded-2xl text-center group transition-all hover:border-[#cf5cff]/40">
            <div class="text-4xl md:text-5xl font-bold gradient-text mb-1" style="font-family: 'Space Grotesk', sans-serif;">$40M+</div>
            <div class="text-xs text-[#b9cacb] uppercase tracking-widest">Raised by clients</div>
        </div>

    </div>
</section>`
  },
  {
    id: "performance",
    name: "Performance Grid",
    filename: "resources/views/components/performance.blade.php",
    description: "Our Bento-Grid showing Velocity, Quality, and Scale cards, enriched with high-contrast, luminous layout patterns.",
    language: "blade",
    code: `<section class="py-24 px-6 max-w-7xl mx-auto space-y-12">
    <div class="text-center">
        <h2 class="text-3xl md:text-5xl font-bold text-white mb-4" style="font-family: 'Space Grotesk', sans-serif;">
            Engineered for <span class="text-[#00f0ff]">Performance</span>
        </h2>
        <p class="text-base md:text-lg text-[#b9cacb] max-w-2xl mx-auto">
            Our methodology combines cutting-edge architecture with rapid iteration cycles.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Velocity Card -->
        <div class="md:col-span-7 glass-card p-10 rounded-3xl flex flex-col justify-between group relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-[#00f0ff]/10 rounded-full blur-[80px] group-hover:bg-[#00f0ff]/20 transition-all duration-500"></div>
            <div>
                <div class="w-16 h-16 rounded-2xl bg-[#00f0ff]/10 flex items-center justify-center mb-8">
                    <span class="material-symbols-outlined text-4xl text-[#00f0ff]">bolt</span>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-[#e5e2e1]" style="font-family: 'Space Grotesk', sans-serif;">Velocity</h3>
                <p class="text-sm text-[#b9cacb] max-w-md leading-relaxed">
                    Our internal frameworks allow us to bypass boilerplate and focus on your core business logic from day one. Shipping faster isn't a goal; it's our standard.
                </p>
            </div>
            <div class="pt-8 flex items-center gap-4">
                <div class="h-1 flex-1 bg-[#2a2a2a] rounded-full overflow-hidden">
                    <div class="h-full bg-[#00f0ff] w-3/4 animate-pulse"></div>
                </div>
                <span class="text-xs font-mono text-[#7df4ff]">OPTIMIZED</span>
            </div>
        </div>

        <!-- Quality Card -->
        <div class="md:col-span-5 glass-card p-10 rounded-3xl flex flex-col justify-between group border-l-[#cf5cff]/20">
            <div>
                <div class="w-16 h-16 rounded-2xl bg-[#cf5cff]/10 flex items-center justify-center mb-8">
                    <span class="material-symbols-outlined text-4xl text-[#cf5cff]">verified</span>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-[#e5e2e1]" style="font-family: 'Space Grotesk', sans-serif;">Quality</h3>
                <p class="text-sm text-[#b9cacb] leading-relaxed">
                    Zero compromise on code integrity. Automated testing and rigorous peer review are baked into every commit.
                </p>
            </div>
            <div class="flex gap-2 pt-6">
                <span class="px-3 py-1 rounded-full bg-[#2a2a2a] text-[#b9cacb] text-xs font-medium">TESTED</span>
                <span class="px-3 py-1 rounded-full bg-[#2a2a2a] text-[#b9cacb] text-xs font-medium">SECURE</span>
                <span class="px-3 py-1 rounded-full bg-[#2a2a2a] text-[#b9cacb] text-xs font-medium">CLEAN</span>
            </div>
        </div>

        <!-- Scale Card -->
        <div class="md:col-span-12 glass-card p-10 rounded-3xl flex flex-col md:flex-row items-center gap-10 group relative overflow-hidden">
            <div class="flex-1 space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-[#79ff5b]/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#79ff5b]">rocket_launch</span>
                </div>
                <h3 class="text-2xl font-bold text-[#e5e2e1]" style="font-family: 'Space Grotesk', sans-serif;">Scale</h3>
                <p class="text-sm text-[#b9cacb] max-w-xl leading-relaxed">
                    From your first 100 users to your first 10 million. We build on cloud-native architectures that expand with your success, ensuring performance never drops as traffic spikes.
                </p>
            </div>
            
            <div class="w-full md:w-1/3 glass-card bg-[#1c1b1b]/60 rounded-2xl p-6 border-[#3b494b]/30">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-[#b9cacb]">Server Load</span>
                        <span class="text-[#79ff5b] text-xs font-semibold">Efficient</span>
                    </div>
                    <div class="grid grid-cols-6 gap-2 items-end h-16">
                        <div class="h-10 bg-[#33fb0a]/20 rounded"></div>
                        <div class="h-14 bg-[#33fb0a]/40 rounded animate-pulse"></div>
                        <div class="h-8 bg-[#33fb0a]/30 rounded"></div>
                        <div class="h-16 bg-[#33fb0a]/60 rounded animate-pulse"></div>
                        <div class="h-12 bg-[#33fb0a]/40 rounded"></div>
                        <div class="h-6 bg-[#33fb0a]/20 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>`
  },
  {
    id: "cta",
    name: "CTA Section",
    filename: "resources/views/components/cta.blade.php",
    description: "The grand call to action section designed with responsive alignment and smooth border glow indicators.",
    language: "blade",
    code: `<section class="py-24 px-6 max-w-7xl mx-auto">
    <div class="relative glass-card bg-gradient-to-br from-[#1c1b1b] to-[#0e0e0e] p-12 md:p-24 rounded-[40px] text-center border-white/5 overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#00f0ff]/5 rounded-full blur-[100px]"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-[#cf5cff]/5 rounded-full blur-[100px]"></div>
        
        <h2 class="relative z-10 text-4xl md:text-6xl font-bold mb-8 leading-tight text-white" style="font-family: 'Space Grotesk', sans-serif;">
            Ready to build the <br class="hidden md:block"/> next big thing?
        </h2>
        
        <p class="relative z-10 text-base md:text-lg text-[#b9cacb] max-w-2xl mx-auto mb-12">
            Join 50+ startups who have scaled their vision with Digital Builder. Our team is ready to deploy.
        </p>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-center gap-6">
            <button class="w-full md:w-auto px-12 py-5 btn-gradient text-[#002022] rounded-2xl text-sm font-extrabold uppercase tracking-widest shadow-xl">
                Schedule a Consultation
            </button>
            <a class="text-[#00dbe9] text-sm font-bold uppercase tracking-widest hover:text-[#00f0ff] transition-colors" href="#">
                View our stack →
            </a>
        </div>
    </div>
</section>`
  },
  {
    id: "footer",
    name: "Footer Component",
    filename: "resources/views/components/footer.blade.php",
    description: "The complete, highly modular footer with copyright text, responsive column link blocks, and a newsletter sign-up.",
    language: "blade",
    code: `<footer class="w-full mt-auto bg-[#0e0e0e] border-t border-[#3b494b]">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 px-6 md:px-16 py-12 max-w-7xl mx-auto">
        
        <div class="md:col-span-4 space-y-6">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-[#00f0ff]" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="25" width="50" height="50" rx="12" stroke="currentColor" stroke-width="8" stroke-dasharray="4 4" />
                    <circle cx="50" cy="50" r="10" fill="#00f0ff" />
                </svg>
                <span class="text-lg font-bold text-[#7df4ff] tracking-tight" style="font-family: 'Space Grotesk', sans-serif;">
                    Digital Builder
                </span>
            </div>
            <p class="text-[#b9cacb] text-sm leading-relaxed max-w-xs">
                Engineering the digital frontier. Fast, scalable, and beautifully crafted experiences for the modern web.
            </p>
        </div>

        <div class="md:col-span-2 space-y-4">
            <h4 class="text-xs font-bold text-white uppercase tracking-widest" style="font-family: monospace;">Platform</h4>
            <ul class="space-y-2 text-sm">
                <li><a class="text-[#b9cacb] hover:text-[#00dbe9] transition-all" href="#">Github</a></li>
                <li><a class="text-[#b9cacb] hover:text-[#00dbe9] transition-all" href="#">Docs</a></li>
                <li><a class="text-[#b9cacb] hover:text-[#00dbe9] transition-all" href="#">Security</a></li>
            </ul>
        </div>

        <div class="md:col-span-2 space-y-4">
            <h4 class="text-xs font-bold text-white uppercase tracking-widest" style="font-family: monospace;">Company</h4>
            <ul class="space-y-2 text-sm">
                <li><a class="text-[#b9cacb] hover:text-[#00dbe9] transition-all" href="#">Twitter</a></li>
                <li><a class="text-[#b9cacb] hover:text-[#00dbe9] transition-all" href="#">Linkedin</a></li>
                <li><a class="text-[#b9cacb] hover:text-[#00dbe9] transition-all" href="#">Privacy</a></li>
            </ul>
        </div>

        <div class="md:col-span-4 space-y-4">
            <h4 class="text-xs font-bold text-white uppercase tracking-widest" style="font-family: monospace;">Stay Connected</h4>
            <form action="#" method="POST" class="flex gap-2 p-1 rounded-xl bg-[#2a2a2a] border border-[#3b494b] focus-within:ring-2 focus-within:ring-[#00f0ff] transition-all">
                @csrf
                <input class="bg-transparent border-none focus:ring-0 text-sm flex-1 px-4 text-[#e5e2e1] placeholder-[#b9cacb]" placeholder="Email address" type="email" required name="email" />
                <button class="bg-[#00f0ff] text-[#002022] px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-[#7df4ff] transition-colors" type="submit">Join</button>
            </form>
        </div>

        <div class="md:col-span-12 pt-12 mt-12 border-t border-[#3b494b] flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-[#b9cacb]">
            <p>© {{ date('Y') }} Digital Builder. Engineered for velocity.</p>
            <div class="flex gap-6">
                <a class="hover:text-[#79ff5b] transition-all" href="#">Terms of Service</a>
                <a class="hover:text-[#79ff5b] transition-all" href="#">Cookies</a>
            </div>
        </div>

    </div>
</footer>`
  },
  {
    id: "tailwindv4",
    name: "Tailwind CSS v4 CSS file Setup",
    filename: "resources/css/app.css",
    description: "The css configuration for projects using Tailwind CSS v4. Standard in Laravel 11+ with the latest Vite presets.",
    language: "css",
    code: `@import "tailwindcss";

@theme {
  --color-secondary-fixed: #f8d8ff;
  --color-on-primary-container: #006970;
  --color-on-error: #690005;
  --color-surface-container-low: #1c1b1b;
  --color-primary: #dbfcff;
  --color-primary-fixed: #7df4ff;
  --color-primary-fixed-dim: #00dbe9;
  --color-on-error-container: #ffdad6;
  --color-on-surface: #e5e2e1;
  --color-primary-container: #00f0ff;
  --color-inverse-surface: #e5e2e1;
  --color-error-container: #93000a;
  --color-error: #ffb4ab;
  --color-surface-container-high: #2a2a2a;
  --color-secondary-container: #cf5cff;
  --color-surface-variant: #353534;
  --color-on-surface-variant: #b9cacb;
  --color-on-secondary: #520071;
  --color-on-secondary-fixed-variant: #74009f;
  --color-tertiary-container: #33fb0a;
  --color-secondary-fixed-dim: #ecb2ff;
  --color-on-primary-fixed: #002022;
  --color-tertiary: #e1ffd1;
  --color-inverse-primary: #006970;
  --color-tertiary-fixed-dim: #2ae500;
  --color-surface-dim: #131313;
  --color-on-tertiary-container: #106e00;
  --color-tertiary-fixed: #79ff5b;
  --on-tertiary-fixed-variant: #095300;
  --on-secondary-container: #480063;
  --on-primary-fixed-variant: #004f54;
  --color-outline-variant: #3b494b;
  --color-outline: #849495;
  --color-on-tertiary: #053900;
  --color-on-secondary-fixed: #320047;
  --color-background: #131313;
  --color-surface-container-highest: #353534;
  --color-surface-bright: #393939;
  --color-inverse-on-surface: #313030;
  --color-on-tertiary-fixed: #022100;
  --color-on-primary: #00363a;
  --color-surface: #131313;
  --color-surface-container-lowest: #0e0e0e;
  --color-surface-container: #201f1f;
  --color-secondary: #ecb2ff;
  --color-surface-tint: #00dbe9;
  --color-on-background: #e5e2e1;

  --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
  --font-display: "Space Grotesk", sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, monospace;
}`
  },
  {
    id: "tailwindv3",
    name: "Tailwind CSS v3 config Setup",
    filename: "tailwind.config.js",
    description: "The tailwind configuration for older Laravel projects using Tailwind CSS v3 and Laravel Mix/Vite.",
    language: "javascript",
    code: `/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "secondary-fixed": "#f8d8ff",
        "on-primary-container": "#006970",
        "on-error": "#690005",
        "surface-container-low": "#1c1b1b",
        "primary": "#dbfcff",
        "primary-fixed": "#7df4ff",
        "primary-fixed-dim": "#00dbe9",
        "on-error-container": "#ffdad6",
        "on-surface": "#e5e2e1",
        "primary-container": "#00f0ff",
        "inverse-surface": "#e5e2e1",
        "error-container": "#93000a",
        "error": "#ffb4ab",
        "surface-container-high": "#2a2a2a",
        "secondary-container": "#cf5cff",
        "surface-variant": "#353534",
        "on-surface-variant": "#b9cacb",
        "on-secondary": "#520071",
        "on-secondary-fixed-variant": "#74009f",
        "tertiary-container": "#33fb0a",
        "secondary-fixed-dim": "#ecb2ff",
        "on-primary-fixed": "#002022",
        "tertiary": "#e1ffd1",
        "inverse-primary": "#006970",
        "tertiary-fixed-dim": "#2ae500",
        "surface-dim": "#131313",
        "on-tertiary-container": "#106e00",
        "tertiary-fixed": "#79ff5b",
        "on-tertiary-fixed-variant": "#095300",
        "on-secondary-container": "#480063",
        "on-primary-fixed-variant": "#004f54",
        "outline-variant": "#3b494b",
        "outline": "#849495",
        "on-tertiary": "#053900",
        "on-secondary-fixed": "#320047",
        "background": "#131313",
        "surface-container-highest": "#353534",
        "surface-bright": "#393939",
        "inverse-on-surface": "#313030",
        "on-tertiary-fixed": "#022100",
        "on-primary": "#00363a",
        "surface": "#131313",
        "surface-container-lowest": "#0e0e0e",
        "surface-container": "#201f1f",
        "secondary": "#ecb2ff",
        "surface-tint": "#00dbe9",
        "on-background": "#e5e2e1"
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
        display: ["Space Grotesk", "sans-serif"],
        mono: ["JetBrains Mono", "monospace"]
      }
    },
  },
  plugins: [],
}`
  }
];
