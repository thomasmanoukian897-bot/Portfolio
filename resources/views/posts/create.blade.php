@extends('layouts.app')

@section('title', 'Write a Post | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 px-6 md:px-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/50 to-transparent pointer-events-none"></div>

        <div class="relative max-w-3xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-primary hover:text-blue-700 transition-colors">
                    &larr; Back to blog
                </a>
            </div>

            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary font-mono mb-4">Share Your Ideas</p>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 font-display tracking-tight">
                    Write a Post
                </h1>
                <p class="mt-3 text-slate-600 leading-relaxed">
                    Publish your thoughts on web development, design, and building digital products.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @include('posts._form', ['categories' => $categories])

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            class="btn-gradient text-white px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all hover:scale-[1.02] active:scale-[0.98]"
                        >
                            Publish Post
                        </button>

                        <a
                            href="{{ route('posts.index') }}"
                            class="px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-slate-600 hover:text-slate-900 transition-colors"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
