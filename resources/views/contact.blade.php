@extends('layouts.app')

@section('title', 'Contact | Digital Builder')

@section('content')
    <section class="relative min-h-[85vh] flex flex-col items-center justify-center pt-16 pb-24 overflow-hidden mesh-gradient">
        <div class="absolute inset-0 animated-grid opacity-60 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-xl px-6">
            <div class="text-center mb-8 space-y-4">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-slate-200 bg-slate-50/80 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-xs text-slate-700 font-bold uppercase tracking-widest font-mono">
                        Get In Touch
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold tracking-tighter text-slate-900 font-display">
                    Let's <span class="gradient-text">Connect</span>
                </h1>

                <p class="text-sm text-slate-600 leading-relaxed">
                    Have a project in mind or a question? Send us a message and we'll respond as soon as we can.
                </p>
            </div>

            <div class="glass-card rounded-2xl p-8 shadow-md">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        <p class="font-semibold">{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold">Please fix the errors below</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Name
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
                            placeholder="Your name"
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
                            autocomplete="email"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="you@company.com"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="subject" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Subject
                        </label>
                        <input
                            id="subject"
                            name="subject"
                            type="text"
                            value="{{ old('subject') }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('subject') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="What is this about?"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="message" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                            Message
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="5"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 resize-y @error('message') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="Tell us about your project or question..."
                        >{{ old('message') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full px-6 py-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm"
                    >
                        Send Message
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-slate-600">
                <a href="{{ route('home') }}" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    &larr; Back to home
                </a>
            </p>
        </div>
    </section>
@endsection
