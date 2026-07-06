@extends('layouts.app')

@section('title', 'Blog | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 px-6 md:px-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/50 to-transparent pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary font-mono mb-4">Insights & Updates</p>
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <h1 class="text-4xl md:text-5xl font-bold text-slate-900 font-display tracking-tight mb-4">
                            Latest Posts
                        </h1>
                        <p class="text-lg text-slate-600 leading-relaxed">
                            Thoughts on web development, design, and building digital products.
                        </p>
                    </div>

                    @auth
                        <a
                            href="{{ route('posts.create') }}"
                            class="inline-flex shrink-0 items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900/95 border border-slate-700 text-white hover:bg-slate-800 transition-all duration-300 hover:scale-[1.05] active:scale-[0.95] shadow-lg shadow-black/20 backdrop-blur-md"
                        >
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 6-6-6-6M12 19h8" />
                            </svg>
                            <span class="text-xs font-bold font-mono tracking-wider">Write a Post</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <section class="px-6 md:px-16 pb-24">
        <div class="max-w-7xl mx-auto">
            @if (session('status'))
                <div class="mb-8 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-950/50 px-4 py-3 text-sm text-green-700 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $indexQuery = array_filter([
                    'category' => $selectedCategory,
                    'sort' => $selectedSort === 'newest' ? null : $selectedSort,
                ]);

                $toggleSortQuery = array_filter([
                    'category' => $selectedCategory,
                    'sort' => $selectedSort === 'newest' ? 'oldest' : null,
                ]);
            @endphp

            <div @class([
                'flex flex-wrap items-center gap-4 mb-8',
                'justify-between' => $categories->isNotEmpty(),
                'justify-end' => $categories->isEmpty(),
            ])>
                @if ($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('posts.index', $indexQuery) }}"
                            @class([
                                'px-4 py-2 rounded-full text-xs font-bold font-mono uppercase tracking-wider transition-colors',
                                'bg-slate-900 text-white shadow-sm' => $selectedCategory === null,
                                'border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-primary' => $selectedCategory !== null,
                            ])
                        >
                            All
                        </a>

                        @foreach ($categories as $category)
                            <a
                                href="{{ route('posts.index', array_filter([
                                    'category' => $category->slug,
                                    'sort' => $selectedSort === 'newest' ? null : $selectedSort,
                                ])) }}"
                                @class([
                                    'px-4 py-2 rounded-full text-xs font-bold font-mono uppercase tracking-wider transition-colors',
                                    'bg-slate-900 text-white shadow-sm' => $selectedCategory === $category->slug,
                                    'border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-primary' => $selectedCategory !== $category->slug,
                                ])
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <a
                    href="{{ route('posts.index', $toggleSortQuery) }}"
                    class="inline-flex shrink-0 items-center gap-2 px-4 py-2 rounded-full text-xs font-bold font-mono uppercase tracking-wider border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-primary transition-colors"
                    title="{{ $selectedSort === 'newest' ? 'Switch to oldest first' : 'Switch to newest first' }}"
                >
                    @if ($selectedSort === 'newest')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-7 7m7-7 7 7" />
                        </svg>
                        Newest first
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0 7-7m-7 7-7-7" />
                        </svg>
                        Oldest first
                    @endif
                </a>
            </div>

            @if ($posts->isNotEmpty())
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 items-start">
                    @foreach ($posts as $post)
                        <article class="group flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <a href="{{ route('posts.show', $post) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100 relative">
                                @if ($post->featuredImageUrl())
                                    <img
                                        src="{{ $post->featuredImageUrl() }}"
                                        alt="{{ $post->title }}"
                                        class="h-full w-full object-cover transition-transform group-hover:scale-[1.02]"
                                    />
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-blue-50/80 to-slate-200 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900">
                                        <div class="absolute inset-0 animated-grid opacity-30"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <x-user-avatar :user="$post->user" size="lg" class="w-24 h-24 text-3xl ring-4 ring-white/50 dark:ring-slate-700/50 shadow-lg" />
                                        </div>
                                    </div>
                                @endif
                            </a>
                            <div class="p-6 flex flex-col">
                                <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
                                    <x-user-avatar :user="$post->user" size="xs" />
                                    <span>{{ $post->user->name }}</span>
                                    <span class="text-slate-300">&middot;</span>
                                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                                        {{ $post->published_at->diffForHumans() }}
                                    </time>
                                </div>

                                @if ($post->categories->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @foreach ($post->categories as $category)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono bg-blue-50 text-blue-700">
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <h2 class="text-xl font-bold text-slate-900 font-display mb-3 group-hover:text-primary transition-colors">
                                    <a href="{{ route('posts.show', $post) }}" class="hover:underline">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                @if ($post->excerpt)
                                    <p class="text-sm text-slate-600 leading-relaxed mb-6">
                                        {{ $post->excerpt }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between gap-4 {{ $post->excerpt ? 'mt-2' : 'mt-4' }}">
                                    <a
                                        href="{{ route('posts.show', $post) }}"
                                        class="inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-primary hover:text-blue-700 transition-colors"
                                    >
                                        Read More
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>

                                    @can('delete', $post)
                                        <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                aria-label="Delete"
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-950 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 transition-colors"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($posts->hasPages())
                    <div class="mt-12">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-slate-200 bg-white px-8 py-16 text-center">
                    <p class="text-slate-500">
                        @if ($selectedCategory)
                            No posts in this category yet.
                        @else
                            No posts published yet. Check back soon.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection
