@extends('layouts.app')

@section('title', 'Blog | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 px-6 md:px-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-100/60 via-blue-50/20 to-transparent dark:from-slate-950/95 dark:via-slate-900/80 dark:to-slate-900/0 pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary font-mono mb-4">Insights & Updates</p>
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-slate-900 font-display tracking-tight mb-4">
                        Latest Posts
                    </h1>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        Thoughts on web development, design, and building digital products.
                    </p>
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
                $allQuery = array_filter([
                    'search' => $search,
                    'sort' => $selectedSort === 'newest' ? null : $selectedSort,
                ]);

                $sortOptions = [
                    'newest' => 'Newest first',
                    'oldest' => 'Oldest first',
                    'most-liked' => 'Most liked',
                    'least-liked' => 'Least liked',
                    'most-commented' => 'Most commented',
                    'least-commented' => 'Least commented',
                    'most-viewed' => 'Most viewed',
                    'least-viewed' => 'Least viewed',
                ];
            @endphp

            <div class="mb-8 flex flex-wrap items-center gap-4">
                <form method="GET" action="{{ route('posts.index') }}" class="max-w-lg">
                    @if ($selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    @endif
                    @if ($selectedSort !== 'newest')
                        <input type="hidden" name="sort" value="{{ $selectedSort }}">
                    @endif
                    <label for="post-search" class="sr-only">Search posts</label>
                    <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2.5 shadow-xs transition-all focus-within:border-transparent focus-within:ring-2 focus-within:ring-blue-600 dark:border-slate-700 dark:bg-slate-900/50">
                        <i class="fa-solid fa-magnifying-glass shrink-0 text-sm text-slate-400 dark:text-slate-500" aria-hidden="true"></i>
                        <input
                            id="post-search"
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search posts..."
                            role="searchbox"
                            autocomplete="off"
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-0 dark:text-slate-100 dark:placeholder-slate-500"
                        />
                    </div>
                </form>

                @auth
                    <a
                        href="{{ route('posts.create') }}"
                        class="inline-flex shrink-0 items-center justify-center gap-2.5 px-4 py-2.5 rounded-full bg-slate-900/95 border border-slate-700 text-white hover:bg-slate-800 transition-all duration-300 hover:scale-[1.05] active:scale-[0.95] shadow-lg shadow-black/20 backdrop-blur-md"
                    >
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 6-6-6-6M12 19h8" />
                        </svg>
                        <span class="text-xs font-bold font-mono tracking-wider">Write a Post</span>
                    </a>
                @endauth
            </div>

            <div @class([
                'flex flex-wrap items-center gap-4 mb-8',
                'justify-between' => $categories->isNotEmpty(),
                'justify-end' => $categories->isEmpty(),
            ])>
                @if ($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('posts.index', $allQuery) }}"
                            @class([
                                'px-4 py-2 rounded-full text-xs font-bold font-mono uppercase tracking-wider transition-colors',
                                'bg-slate-900 text-white shadow-sm' => $selectedCategory === null,
                                'border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-primary' => $selectedCategory !== null,
                            ])
                        >
                            All <span class="tabular-nums opacity-70">({{ $totalPostsCount }})</span>
                        </a>

                        @foreach ($categories as $category)
                            <a
                                href="{{ route('posts.index', array_filter([
                                    'search' => $search,
                                    'category' => $category->slug,
                                    'sort' => $selectedSort === 'newest' ? null : $selectedSort,
                                ])) }}"
                                @class([
                                    'px-4 py-2 rounded-full text-xs font-bold font-mono uppercase tracking-wider transition-colors',
                                    'bg-slate-900 text-white shadow-sm' => $selectedCategory === $category->slug,
                                    'border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-primary' => $selectedCategory !== $category->slug,
                                ])
                            >
                                {{ $category->name }} <span class="tabular-nums opacity-70">({{ $category->posts_count }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <form method="GET" action="{{ route('posts.index') }}" class="shrink-0">
                    @if ($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    @if ($selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    @endif

                    <label for="post-sort" class="sr-only">Sort posts</label>
                    <select
                        id="post-sort"
                        name="sort"
                        onchange="this.form.submit()"
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold font-mono uppercase tracking-wider text-slate-600 transition-colors hover:border-blue-300 hover:text-primary focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                    >
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedSort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if ($posts->isNotEmpty())
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 items-start">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" />
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
                        @if ($search)
                            No posts match your search.
                        @elseif ($selectedCategory)
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
