@extends('layouts.app')

@section('title', 'All From Today | Digital Builder')

@section('content')
    <section class="pt-24 pb-16 px-6 md:px-16">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100 font-display tracking-tight uppercase">
                        All From Today
                    </h1>
                    <div class="mt-4 border-b border-slate-200 dark:border-slate-700"></div>
                </div>

                @if ($posts->isNotEmpty())
                    <div
                        class="inline-flex items-center rounded-full border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-900/50"
                        role="group"
                        aria-label="Post layout"
                    >
                        <button
                            type="button"
                            data-posts-view-toggle="grid"
                            aria-pressed="true"
                            aria-label="Grid view"
                            class="inline-flex items-center justify-center rounded-full border border-transparent px-3 py-1.5 text-slate-600 transition-colors hover:border-blue-300 hover:text-primary"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            data-posts-view-toggle="list"
                            aria-pressed="false"
                            aria-label="List view"
                            class="inline-flex items-center justify-center rounded-full border border-transparent px-3 py-1.5 text-slate-600 transition-colors hover:border-blue-300 hover:text-primary"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75v-.008Zm0 5.25h.007v.008H3.75v-.008Z" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            @if ($posts->isNotEmpty())
                <div id="posts-feed" data-posts-view="grid" class="mt-8">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>

                @if ($posts->hasPages())
                    <div class="mt-10">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <div class="py-16 text-center">
                    <p class="text-slate-500 dark:text-slate-400">
                        No posts published today yet. Check back later.
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection
