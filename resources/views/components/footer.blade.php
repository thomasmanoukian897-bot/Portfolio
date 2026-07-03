<footer class="w-full mt-auto bg-slate-100 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 px-6 md:px-16 py-12 max-w-7xl mx-auto">

        <div class="md:col-span-4 space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-200">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                    </svg>
                </div>
                <span class="text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight font-display">
                    Digital Builder
                </span>
            </div>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed max-w-xs">
                Engineering the digital frontier. Fast, scalable, and beautifully crafted experiences for the modern web.
            </p>
        </div>

        <div class="md:col-span-2 space-y-4">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">Platform</h4>
            <ul class="space-y-2 text-sm">
                <li><a class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all font-medium" href="#">Github</a></li>
                <li><a class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all font-medium" href="#">Docs</a></li>
                <li><a class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all font-medium" href="#">Security</a></li>
            </ul>
        </div>

        <div class="md:col-span-2 space-y-4">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">Company</h4>
            <ul class="space-y-2 text-sm">
                <li><a class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all font-medium" href="#">Twitter</a></li>
                <li><a class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all font-medium" href="#">Linkedin</a></li>
                <li><a class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all font-medium" href="#">Privacy</a></li>
            </ul>
        </div>

        <div class="md:col-span-4 space-y-4">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">Stay Connected</h4>
            <form action="#" method="POST" class="flex gap-2 p-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-transparent transition-all shadow-xs">
                @csrf
                <input
                    class="bg-transparent border-none focus:ring-0 text-sm flex-1 px-4 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none"
                    placeholder="Email address"
                    type="email"
                    name="email"
                    required
                />
                <button class="bg-slate-900 text-white hover:bg-slate-800 px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider transition-colors" type="submit">
                    Join
                </button>
            </form>
        </div>

        <div class="md:col-span-12 pt-12 mt-12 border-t border-slate-200 dark:border-slate-700 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
            <p>&copy; {{ date('Y') }} Digital Builder. Engineered for velocity.</p>
            <div class="flex gap-6">
                <a class="hover:text-blue-600 transition-all font-semibold" href="#">Terms of Service</a>
                <a class="hover:text-blue-600 transition-all font-semibold" href="#">Cookies</a>
            </div>
        </div>

    </div>
</footer>
