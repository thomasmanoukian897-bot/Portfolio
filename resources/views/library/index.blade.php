@extends('layouts.app')

@section('title', 'Library | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 px-6 md:px-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-100/60 via-blue-50/20 to-transparent dark:from-slate-950/95 dark:via-slate-900/80 dark:to-slate-900/0 pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary font-mono mb-4">Your Collection</p>
                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-slate-100 font-display tracking-tight mb-4">
                    Library
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    Your posts, liked articles, saved bookmarks, reading history, and session bookings in one place.
                </p>
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
                $sections = [
                    'posts' => [
                        'label' => 'Your Posts',
                        'description' => 'Posts you have published.',
                        'icon' => 'fa-pen',
                    ],
                    'liked' => [
                        'label' => 'Liked Posts',
                        'description' => 'Posts you have liked.',
                        'icon' => 'fa-heart',
                    ],
                    'bookmarks' => [
                        'label' => 'Your Bookmarks',
                        'description' => 'Posts you have saved for later.',
                        'icon' => 'fa-bookmark',
                    ],
                    'history' => [
                        'label' => 'Reading History',
                        'description' => 'Articles you have read.',
                        'icon' => 'fa-clock-rotate-left',
                    ],
                    'bookings' => [
                        'label' => 'Your Bookings',
                        'description' => 'Sessions you have booked.',
                        'icon' => 'fa-calendar-check',
                    ],
                ];

                $emptyMessages = [
                    'posts' => 'You have not published any posts yet.',
                    'liked' => 'You have not liked any posts yet.',
                    'bookmarks' => 'You have not bookmarked any posts yet.',
                    'history' => 'You have not read any articles yet.',
                    'bookings' => 'You have not booked any sessions yet.',
                ];
            @endphp

            <div class="mb-8 flex flex-wrap gap-2">
                @foreach ($sections as $key => $info)
                    <a
                        href="{{ route('library.index', ['section' => $key]) }}"
                        @class([
                            'inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-bold font-mono uppercase tracking-wider transition-colors',
                            'bg-slate-900 text-white shadow-sm dark:bg-slate-100 dark:text-slate-900' => $section === $key,
                            'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:border-blue-300 hover:text-primary' => $section !== $key,
                        ])
                    >
                        <i class="fa-solid {{ $info['icon'] }}" aria-hidden="true"></i>
                        {{ $info['label'] }}
                        <span class="tabular-nums opacity-70">({{ $sectionCounts[$key] }})</span>
                    </a>
                @endforeach
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 font-display">
                    {{ $sections[$section]['label'] }}
                </h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ $sections[$section]['description'] }}
                </p>
            </div>

            @if ($section === 'bookings')
                @if ($bookings->isNotEmpty())
                    <div class="grid gap-6 md:grid-cols-2 items-start">
                        @foreach ($bookings as $reservation)
                            <x-reservation-card :reservation="$reservation" />
                        @endforeach
                    </div>

                    @if ($bookings->hasPages())
                        <div class="mt-12">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-8 py-16 text-center">
                        <p class="text-slate-500 dark:text-slate-400 mb-6">
                            {{ $emptyMessages['bookings'] }}
                        </p>
                        <a
                            href="{{ route('reservations.index') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider btn-gradient text-white active:scale-95 transition-transform"
                        >
                            <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                            Book a Session
                        </a>
                    </div>
                @endif
            @elseif (isset($posts) && $posts->isNotEmpty())
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
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-8 py-16 text-center">
                    <p class="text-slate-500 dark:text-slate-400 mb-6">
                        {{ $emptyMessages[$section] }}
                    </p>

                    @if ($section === 'posts')
                        <a
                            href="{{ route('posts.create') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider btn-gradient text-white active:scale-95 transition-transform"
                        >
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                            Write a Post
                        </a>
                    @else
                        <a
                            href="{{ route('posts.index') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider btn-gradient text-white active:scale-95 transition-transform"
                        >
                            Browse Posts
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
