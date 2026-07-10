@extends('layouts.app')

@section('title', $profileUser->name.' | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-8 px-6 md:px-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-100/60 via-blue-50/20 to-transparent dark:from-slate-950/95 dark:via-slate-900/80 dark:to-slate-900/0 pointer-events-none"></div>

        <div class="relative max-w-4xl mx-auto">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-950/50 px-4 py-3 text-sm text-green-700 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-10">
                <x-user-avatar :user="$profileUser" size="xl" class="ring-4 ring-white dark:ring-slate-800 shadow-lg" />

                <div class="flex-1 w-full text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-4">
                        <h1 class="text-xl md:text-2xl font-semibold text-slate-900 dark:text-slate-100">
                            {{ $profileUser->handle() }}
                        </h1>

                        <div class="flex items-center justify-center sm:justify-start gap-2">
                            @if ($isOwnProfile)
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="inline-flex items-center justify-center px-5 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600 transition-colors"
                                >
                                    Edit profile
                                </a>
                            @else
                                @auth
                                    <form method="POST" action="{{ route('users.follow.toggle', $profileUser) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            @class([
                                                'inline-flex items-center justify-center px-5 py-2 rounded-lg text-sm font-semibold transition-colors',
                                                'bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-500' => $isFollowedByViewer,
                                                'bg-primary hover:bg-primary/90 text-white border border-primary' => ! $isFollowedByViewer,
                                            ])
                                        >
                                            {{ $isFollowedByViewer ? 'Unfollow' : 'Follow' }}
                                        </button>
                                    </form>
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center px-5 py-2 rounded-lg text-sm font-semibold bg-primary hover:bg-primary/90 text-white border border-primary transition-colors"
                                    >
                                        Follow
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>

                    <p class="text-base font-medium text-slate-900 dark:text-slate-100 mb-4">
                        {{ $profileUser->name }}
                    </p>

                    @php
                        $connectionStatClass = 'cursor-pointer rounded-lg px-2.5 py-1 -mx-2.5 -my-1 text-slate-600 underline underline-offset-2 decoration-slate-400/70 transition-colors hover:bg-slate-100 hover:text-primary hover:decoration-primary active:scale-95 dark:text-slate-400 dark:decoration-slate-500 dark:hover:bg-slate-800 dark:hover:text-blue-400 dark:hover:decoration-blue-400';
                    @endphp

                    <div class="flex items-center justify-center sm:justify-start gap-6 mb-4 text-sm">
                        <span class="text-slate-600 dark:text-slate-400"><span class="font-semibold text-slate-900 dark:text-slate-100 tabular-nums">{{ $profileUser->posts_count }}</span> {{ Str::ucfirst(Str::plural('post', $profileUser->posts_count)) }}</span>
                        <button
                            type="button"
                            data-user-connections-open="followers"
                            class="{{ $connectionStatClass }}"
                        >
                            <span class="font-semibold text-slate-900 dark:text-slate-100 tabular-nums">{{ $profileUser->followers_count }}</span> {{ Str::ucfirst(Str::plural('follower', $profileUser->followers_count)) }}
                        </button>
                        <button
                            type="button"
                            data-user-connections-open="following"
                            class="{{ $connectionStatClass }}"
                        >
                            <span class="font-semibold text-slate-900 dark:text-slate-100 tabular-nums">{{ $profileUser->following_count }}</span> Following
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="px-6 md:px-16 pb-24">
        <div class="max-w-4xl mx-auto">
            <div class="flex border-t border-slate-200 dark:border-slate-700">
                <a
                    href="{{ route('users.show', ['user' => $profileUser, 'section' => 'posts']) }}"
                    @class([
                        'flex flex-1 items-center justify-center gap-2 py-4 text-xs font-semibold uppercase tracking-wider border-t-2 -mt-px transition-colors',
                        'border-slate-900 dark:border-slate-100 text-slate-900 dark:text-slate-100' => $section === 'posts',
                        'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $section !== 'posts',
                    ])
                    aria-label="Posts"
                >
                    <i class="fa-solid fa-table-cells text-base" aria-hidden="true"></i>
                    <span class="hidden sm:inline">Posts</span>
                </a>

                @if (in_array('liked', $allowedSections, true))
                    <a
                        href="{{ route('users.show', ['user' => $profileUser, 'section' => 'liked']) }}"
                        @class([
                            'flex flex-1 items-center justify-center gap-2 py-4 text-xs font-semibold uppercase tracking-wider border-t-2 -mt-px transition-colors',
                            'border-slate-900 dark:border-slate-100 text-slate-900 dark:text-slate-100' => $section === 'liked',
                            'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $section !== 'liked',
                        ])
                        aria-label="Liked posts"
                    >
                        <i class="fa-solid fa-heart text-base" aria-hidden="true"></i>
                        <span class="hidden sm:inline">Liked</span>
                    </a>
                @endif

                @if (in_array('bookmarks', $allowedSections, true))
                    <a
                        href="{{ route('users.show', ['user' => $profileUser, 'section' => 'bookmarks']) }}"
                        @class([
                            'flex flex-1 items-center justify-center gap-2 py-4 text-xs font-semibold uppercase tracking-wider border-t-2 -mt-px transition-colors',
                            'border-slate-900 dark:border-slate-100 text-slate-900 dark:text-slate-100' => $section === 'bookmarks',
                            'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' => $section !== 'bookmarks',
                        ])
                        aria-label="Bookmarked posts"
                    >
                        <i class="fa-solid fa-bookmark text-base" aria-hidden="true"></i>
                        <span class="hidden sm:inline">Saved</span>
                    </a>
                @endif
            </div>

            @php
                $emptyMessages = [
                    'posts' => $isOwnProfile
                        ? 'You have not published any posts yet.'
                        : $profileUser->name.' has not published any posts yet.',
                    'liked' => $isOwnProfile
                        ? 'You have not liked any posts yet.'
                        : $profileUser->name.' has not liked any posts yet.',
                    'bookmarks' => $isOwnProfile
                        ? 'You have not bookmarked any posts yet.'
                        : $profileUser->name.' has not saved any posts yet.',
                ];
            @endphp

            @if ($posts->isNotEmpty())
                <div class="mt-8 grid gap-6 sm:grid-cols-2">
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
                <div class="mt-8 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-8 py-16 text-center">
                    <p class="text-slate-500 dark:text-slate-400 mb-6">
                        {{ $emptyMessages[$section] }}
                    </p>

                    @if ($isOwnProfile && $section === 'posts')
                        <a
                            href="{{ route('posts.create') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider btn-gradient text-white active:scale-95 transition-transform"
                        >
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                            Write a Post
                        </a>
                    @elseif ($isOwnProfile)
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

    <x-user-connections-modal :profile-user="$profileUser" />
@endsection
