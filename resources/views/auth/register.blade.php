@extends('layouts.app')

@section('title', 'Create Account | Digital Builder')

@section('content')
    <section class="relative min-h-[85vh] flex flex-col items-center justify-center pt-16 pb-24 overflow-hidden mesh-gradient">
        <div class="absolute inset-0 animated-grid opacity-60 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-md px-6">
            <div class="text-center mb-8 space-y-4">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-slate-200 bg-slate-50/80 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-xs text-slate-700 font-bold uppercase tracking-widest font-mono">
                        Join the Platform
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold tracking-tighter text-slate-900 font-display">
                    Create Your <span class="gradient-text">Account</span>
                </h1>

                <p class="text-sm text-slate-600 leading-relaxed">
                    Get started with Digital Builder and ship production-ready products faster.
                </p>
            </div>

            <div class="glass-card rounded-2xl p-8 shadow-md">
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold">Unable to create account</p>
                        <p class="mt-1">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Full Name
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('name') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="Jane Doe"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Email Address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="you@company.com"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="••••••••"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password_confirmation') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="••••••••"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full px-6 py-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm"
                    >
                        Create Account
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-slate-600">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    Sign in
                </a>
            </p>

            <p class="mt-3 text-center text-sm text-slate-600">
                <a href="{{ route('home') }}" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    &larr; Back to home
                </a>
            </p>
        </div>
    </section>
@endsection
