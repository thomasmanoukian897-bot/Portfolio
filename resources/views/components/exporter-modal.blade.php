@php
    $exporterTemplates = [
        [
            'id' => 'layout',
            'name' => 'Master Layout',
            'filename' => 'resources/views/layouts/app.blade.php',
            'description' => 'The primary HTML5 shell for your Laravel application. Loads Tailwind CSS, Google Fonts, and outlines the layout with dynamic content slots.',
            'path' => resource_path('views/layouts/app.blade.php'),
        ],
        [
            'id' => 'index',
            'name' => 'Landing Page View',
            'filename' => 'resources/views/index.blade.php',
            'description' => 'The primary page template combining Hero, Stats, Performance, and CTA components.',
            'path' => resource_path('views/index.blade.php'),
        ],
        [
            'id' => 'nav',
            'name' => 'Navbar Component',
            'filename' => 'resources/views/components/nav.blade.php',
            'description' => 'The global navigation header with blur effects, menu items, and call-to-action buttons.',
            'path' => resource_path('views/components/nav.blade.php'),
        ],
        [
            'id' => 'hero',
            'name' => 'Hero Section',
            'filename' => 'resources/views/components/hero.blade.php',
            'description' => 'The hero banner with mesh animations, action buttons, and a floating code element.',
            'path' => resource_path('views/components/hero.blade.php'),
        ],
        [
            'id' => 'stats',
            'name' => 'Stats Grid',
            'filename' => 'resources/views/components/stats.blade.php',
            'description' => 'Metric cards with hover interactions.',
            'path' => resource_path('views/components/stats.blade.php'),
        ],
        [
            'id' => 'performance',
            'name' => 'Performance Grid',
            'filename' => 'resources/views/components/performance.blade.php',
            'description' => 'Bento grid showing Velocity, Quality, and Scale value propositions.',
            'path' => resource_path('views/components/performance.blade.php'),
        ],
        [
            'id' => 'cta',
            'name' => 'CTA Section',
            'filename' => 'resources/views/components/cta.blade.php',
            'description' => 'Call to action section with consultation button.',
            'path' => resource_path('views/components/cta.blade.php'),
        ],
        [
            'id' => 'footer',
            'name' => 'Footer Component',
            'filename' => 'resources/views/components/footer.blade.php',
            'description' => 'Footer with link columns and newsletter signup.',
            'path' => resource_path('views/components/footer.blade.php'),
        ],
        [
            'id' => 'tailwindv4',
            'name' => 'Tailwind CSS v4 Setup',
            'filename' => 'resources/css/app.css',
            'description' => 'Tailwind v4 theme configuration with design tokens and utility classes.',
            'path' => resource_path('css/app.css'),
        ],
    ];

    $exporterTemplates = array_map(function (array $template): array {
        $template['code'] = is_file($template['path'])
            ? file_get_contents($template['path'])
            : '';

        return $template;
    }, $exporterTemplates);
@endphp

<div
    id="exporter-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 md:p-6 bg-black/80 backdrop-blur-md"
    role="dialog"
    aria-modal="true"
    aria-labelledby="exporter-modal-title"
>
    <div class="absolute inset-0" data-exporter-close></div>

    <div class="relative w-full max-w-5xl h-[85vh] md:h-[80vh] bg-white border border-slate-200 rounded-3xl overflow-hidden flex flex-col shadow-xl z-10">
        <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-200">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5 3 12m0 0 3.75 4.5M3 12h18" />
                    </svg>
                </div>
                <div>
                    <h3 id="exporter-modal-title" class="text-base font-bold text-slate-900 font-display">Laravel & Blade Template Exporter</h3>
                    <p class="text-xs text-slate-500 font-mono font-medium">Convert layouts directly for your web app</p>
                </div>
            </div>

            <button type="button" data-exporter-close class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors text-slate-500 hover:text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-3 bg-white border-b border-slate-200">
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                <button type="button" data-exporter-tab="code" class="exporter-tab flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-slate-900 text-white shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Blade Templates
                </button>
                <button type="button" data-exporter-tab="guide" class="exporter-tab flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-950">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                    </svg>
                    Setup Guide
                </button>
            </div>

            <div id="exporter-file-select" class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-mono font-bold hidden sm:inline">Active File:</span>
                <select id="exporter-template-select" class="bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @foreach ($exporterTemplates as $template)
                        <option value="{{ $template['id'] }}">{{ $template['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50">
            <div id="exporter-panel-code" class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full items-stretch">
                <div class="hidden lg:block lg:col-span-3 space-y-2 max-h-[50vh] overflow-y-auto pr-2">
                    @foreach ($exporterTemplates as $template)
                        <button
                            type="button"
                            data-exporter-template="{{ $template['id'] }}"
                            class="exporter-template-btn w-full text-left p-3 rounded-xl transition-all border {{ $loop->first ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-slate-200 hover:bg-slate-100 text-slate-600' }}"
                        >
                            <div class="font-bold text-xs text-slate-800 mb-0.5">{{ $template['name'] }}</div>
                            <div class="text-[10px] font-mono opacity-80 overflow-hidden text-ellipsis whitespace-nowrap">{{ $template['filename'] }}</div>
                        </button>
                    @endforeach
                </div>

                <div class="lg:col-span-9 flex flex-col space-y-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2 shadow-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span id="exporter-filename" class="text-xs font-mono text-blue-600 font-bold">{{ $exporterTemplates[0]['filename'] }}</span>
                            <div class="flex items-center gap-2">
                                <button type="button" id="exporter-download" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Download</span>
                                </button>
                                <button type="button" id="exporter-copy" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 1.927-.184" />
                                    </svg>
                                    <span id="exporter-copy-label">Copy Code</span>
                                </button>
                            </div>
                        </div>
                        <p id="exporter-description" class="text-xs text-slate-600">{{ $exporterTemplates[0]['description'] }}</p>
                    </div>

                    <div class="flex-1 bg-slate-950 rounded-2xl border border-slate-800 relative p-4 max-h-[35vh] md:max-h-[42vh] overflow-auto">
                        <pre id="exporter-code" class="text-xs text-slate-300 leading-relaxed select-all font-mono"><code>{{ $exporterTemplates[0]['code'] }}</code></pre>
                    </div>
                </div>
            </div>

            <div id="exporter-panel-guide" class="hidden max-w-3xl mx-auto space-y-8 py-4">
                <div class="space-y-2">
                    <h4 class="text-lg font-bold text-slate-900 font-display">How to Integrate with your Laravel Project</h4>
                    <p class="text-sm text-slate-600 font-medium">
                        Follow these structured instructions to copy the premium Digital Builder template assets into your local Laravel app.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm shrink-0">1</div>
                        <div class="space-y-1.5">
                            <h5 class="font-semibold text-slate-800 text-sm">Create layout & Blade files</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                In your Laravel directory, navigate to <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">resources/views</code>. Create the master shell at <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">layouts/app.blade.php</code> and the landing page at <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">index.blade.php</code>.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm shrink-0">2</div>
                        <div class="space-y-1.5">
                            <h5 class="font-semibold text-slate-800 text-sm">Organize Blade Components</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Inside <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">resources/views/components</code>, create partials for nav, hero, stats, performance, cta, and footer.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm shrink-0">3</div>
                        <div class="space-y-1.5">
                            <h5 class="font-semibold text-slate-800 text-sm">Setup Tailwind CSS config</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                For Tailwind v4, copy the theme parameters into <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">resources/css/app.css</code>.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm shrink-0">4</div>
                        <div class="space-y-1.5">
                            <h5 class="font-semibold text-slate-800 text-sm">Configure routing</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">Map a route in <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">routes/web.php</code>:</p>
                            <div class="bg-slate-900 p-3 rounded-lg border border-slate-800 font-mono text-[11px] text-slate-300 leading-relaxed">Route::get('/', function () {
    return view('index');
});</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 font-medium">
            <span>All assets are designed to render cleanly using standard Blade template compilation.</span>
            <span class="hidden sm:inline">Digital Builder Exporter &bull; Laravel presets</span>
        </div>
    </div>
</div>

<script type="application/json" id="exporter-templates-data">@json($exporterTemplates)</script>
