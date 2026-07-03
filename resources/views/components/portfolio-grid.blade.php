<section class="py-16 px-6 max-w-7xl mx-auto space-y-12">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3 font-display">
                Featured <span class="text-blue-600">Projects</span>
            </h2>
            <p class="text-base text-slate-600 max-w-xl">
                Real products built for real users — from first deploy to millions of requests.
            </p>
        </div>
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter projects by category">
            <button type="button" data-portfolio-filter="all" aria-pressed="true" class="px-4 py-2 rounded-full bg-slate-900 text-white text-xs font-bold font-mono uppercase tracking-wider hover:border-blue-300 transition-colors cursor-pointer">All</button>
            <button type="button" data-portfolio-filter="saas" aria-pressed="false" class="px-4 py-2 rounded-full glass-card text-slate-600 text-xs font-bold font-mono uppercase tracking-wider hover:border-blue-300 transition-colors cursor-pointer">SaaS</button>
            <button type="button" data-portfolio-filter="fintech" aria-pressed="false" class="px-4 py-2 rounded-full glass-card text-slate-600 text-xs font-bold font-mono uppercase tracking-wider hover:border-blue-300 transition-colors cursor-pointer">Fintech</button>
            <button type="button" data-portfolio-filter="e-commerce" aria-pressed="false" class="px-4 py-2 rounded-full glass-card text-slate-600 text-xs font-bold font-mono uppercase tracking-wider hover:border-blue-300 transition-colors cursor-pointer">E-Commerce</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <article data-portfolio-category="fintech" class="md:col-span-7 glass-card rounded-3xl overflow-hidden group transition-all duration-300 hover:border-blue-300 hover:shadow-md">
            <div class="relative h-64 md:h-80 bg-gradient-to-br from-slate-900 via-blue-900 to-blue-600 overflow-hidden">
                <div class="absolute inset-0 animated-grid opacity-30"></div>
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="glass-card bg-white/10 backdrop-blur-md border-white/20 p-4 rounded-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                                </svg>
                            </div>
                            <span class="text-xs font-mono font-bold text-blue-200 uppercase tracking-widest">Fintech</span>
                        </div>
                        <h3 class="text-xl md:text-2xl font-bold text-white font-display">Payflow</h3>
                    </div>
                </div>
            </div>
            <div class="p-8">
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    A B2B payment orchestration platform processing $12M+ monthly. Built with Laravel, real-time webhooks, and multi-currency ledger architecture.
                </p>
                <div class="flex flex-wrap gap-2 mb-6">
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-xs font-medium font-mono">Laravel</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Vue.js</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">PostgreSQL</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">AWS</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div class="flex gap-6">
                        <div>
                            <div class="text-lg font-bold text-slate-900 font-display">3.2M</div>
                            <div class="text-[10px] text-on-surface-variant uppercase tracking-widest font-mono">Transactions/mo</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-slate-900 font-display">99.99%</div>
                            <div class="text-[10px] text-on-surface-variant uppercase tracking-widest font-mono">Uptime</div>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-blue-600 uppercase tracking-wider group-hover:translate-x-1 transition-transform">View Case →</span>
                </div>
            </div>
        </article>

        <article data-portfolio-category="saas" class="md:col-span-5 glass-card rounded-3xl overflow-hidden group transition-all duration-300 hover:border-slate-400 hover:shadow-md">
            <div class="relative h-48 bg-gradient-to-br from-emerald-600 to-teal-800 overflow-hidden">
                <div class="absolute inset-0 animated-grid opacity-20"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-mono font-bold uppercase tracking-widest border border-white/20">SaaS</span>
                </div>
            </div>
            <div class="p-8">
                <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">TeamPulse</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Async team analytics dashboard for remote-first companies. Shipped MVP in 3 weeks, now serving 200+ teams globally.
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-medium font-mono">Livewire</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Tailwind</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Redis</span>
                </div>
            </div>
        </article>

        <article data-portfolio-category="e-commerce" class="md:col-span-4 glass-card rounded-3xl overflow-hidden group transition-all duration-300 hover:border-blue-300 hover:shadow-md">
            <div class="relative h-44 bg-gradient-to-br from-violet-600 to-purple-900 overflow-hidden">
                <div class="absolute inset-0 animated-grid opacity-20"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-mono font-bold uppercase tracking-widest border border-white/20">E-Commerce</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-2 font-display">MerchLab</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Custom merchandise platform with real-time inventory sync and print-on-demand fulfillment.
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-violet-50 text-violet-700 border border-violet-100 text-xs font-medium font-mono">Laravel</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Stripe</span>
                </div>
            </div>
        </article>

        <article data-portfolio-category="saas" class="md:col-span-4 glass-card rounded-3xl overflow-hidden group transition-all duration-300 hover:border-slate-400 hover:shadow-md">
            <div class="relative h-44 bg-gradient-to-br from-amber-500 to-orange-700 overflow-hidden">
                <div class="absolute inset-0 animated-grid opacity-20"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-mono font-bold uppercase tracking-widest border border-white/20">SaaS</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-2 font-display">DocuSign Pro</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Document workflow automation with e-signatures, audit trails, and enterprise SSO integration.
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100 text-xs font-medium font-mono">React</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Node.js</span>
                </div>
            </div>
        </article>

        <article data-portfolio-category="healthtech" class="md:col-span-4 glass-card rounded-3xl overflow-hidden group transition-all duration-300 hover:border-emerald-300 hover:shadow-md">
            <div class="relative h-44 bg-gradient-to-br from-cyan-500 to-blue-700 overflow-hidden">
                <div class="absolute inset-0 animated-grid opacity-20"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-mono font-bold uppercase tracking-widest border border-white/20">Healthtech</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-2 font-display">VitalTrack</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    HIPAA-compliant patient monitoring portal with real-time vitals dashboards for care teams.
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-cyan-50 text-cyan-700 border border-cyan-100 text-xs font-medium font-mono">Laravel</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">WebSockets</span>
                </div>
            </div>
        </article>

        <article data-portfolio-category="infrastructure" class="md:col-span-12 glass-card rounded-3xl overflow-hidden group transition-all duration-300 hover:border-blue-300 hover:shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="relative h-56 md:h-auto bg-gradient-to-br from-slate-800 to-slate-950 overflow-hidden">
                    <div class="absolute inset-0 animated-grid opacity-30"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-6xl font-bold text-white/10 font-display">API</div>
                        </div>
                    </div>
                </div>
                <div class="p-10 flex flex-col justify-center">
                    <span class="text-xs font-mono font-bold text-blue-600 uppercase tracking-widest mb-3">Infrastructure</span>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4 font-display">CloudScale API Gateway</h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-6">
                        High-throughput API gateway handling 50K requests/sec for a logistics startup. Built with rate limiting, OAuth 2.0, and auto-scaling on Kubernetes — reduced infrastructure costs by 40%.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Go</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Kubernetes</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">gRPC</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">Prometheus</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold font-mono">-40% infra cost</span>
                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold font-mono">50K req/sec</span>
                    </div>
                </div>
            </div>
        </article>
    </div>
</section>
