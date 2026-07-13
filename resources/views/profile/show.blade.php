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
                            {{ $profileUser->handle }}
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

                                    @if ($isFollowedByViewer)
                                        <form method="POST" action="{{ route('users.post-subscription.toggle', $profileUser) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                @class([
                                                    'inline-flex items-center justify-center w-10 h-10 rounded-lg border transition-colors',
                                                    'bg-primary border-primary text-white hover:bg-primary/90' => $isSubscribedToPostsByViewer,
                                                    'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600' => ! $isSubscribedToPostsByViewer,
                                                ])
                                                aria-label="{{ $isSubscribedToPostsByViewer ? 'Turn off post notifications' : 'Get notified when this user posts' }}"
                                                title="{{ $isSubscribedToPostsByViewer ? 'Post notifications on' : 'Notify me when they post' }}"
                                            >
                                                <i @class([
                                                    'fa-solid fa-bell' => $isSubscribedToPostsByViewer,
                                                    'fa-regular fa-bell' => ! $isSubscribedToPostsByViewer,
                                                ]) aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center px-5 py-2 rounded-lg text-sm font-semibold bg-primary hover:bg-primary/90 text-white border border-primary transition-colors"
                                    >
                                        Follow
                                    </a>
                                @endauth

                                <div class="relative" data-profile-dropdown>
                                    <button
                                        type="button"
                                        data-profile-dropdown-toggle
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        aria-label="Profile options"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                                    >
                                        <i class="fa-solid fa-ellipsis" aria-hidden="true"></i>
                                    </button>

                                    <div
                                        data-profile-dropdown-menu
                                        class="hidden absolute left-1/2 sm:left-auto sm:right-0 -translate-x-1/2 sm:translate-x-0 mt-2 w-56 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg py-1 z-50"
                                    >
                                        <button
                                            type="button"
                                            data-copy-url="{{ url(route('users.show', $profileUser)) }}"
                                            data-profile-dropdown-close
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-left"
                                        >
                                            <span data-copy-label>Copy link to profile</span>
                                        </button>

                                        @auth
                                            <form method="POST" action="{{ route('messages.store') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $profileUser->id }}" />
                                                <button
                                                    type="submit"
                                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-left"
                                                >
                                                    Start Messaging
                                                </button>
                                            </form>
                                        @else
                                            <a
                                                href="{{ route('login') }}"
                                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                            >
                                                Start Messaging
                                            </a>
                                        @endauth

                                        @auth
                                            <form method="POST" action="{{ route('users.block', $profileUser) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left"
                                                >
                                                    Block this author
                                                </button>
                                            </form>
                                        @else
                                            <a
                                                href="{{ route('login') }}"
                                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                            >
                                                Block this author
                                            </a>
                                        @endauth
                                    </div>
                                </div>
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
                <div class="mt-6 flex justify-end">
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
                </div>

                <div id="posts-feed" data-posts-view="grid" class="mt-6">
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
