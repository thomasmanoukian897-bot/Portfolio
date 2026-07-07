@extends('layouts.app')

@section('title', "{$post->title} | Digital Builder")

@section('content')
    <article class="relative pt-24 pb-24 px-6 md:px-16">
        <div class="max-w-3xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back to posts
                </a>
            </div>

            @if (session('status'))
                <div class="mb-8 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <header class="mb-10">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <x-user-avatar :user="$post->user" size="sm" />
                        <span>{{ $post->user->name }}</span>
                        <span class="text-slate-300">&middot;</span>
                        <time datetime="{{ $post->published_at->toIso8601String() }}">
                            {{ $post->published_at->format('H:i / d.m.Y') }}
                        </time>
                    </div>

                    @can('delete', $post)
                        <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                aria-label="Delete"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-950 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 transition-colors"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    @endcan
                </div>

                @if ($post->categories->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach ($post->categories as $category)
                            <a
                                href="{{ route('posts.index', ['category' => $category->slug]) }}"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest font-mono bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 font-display tracking-tight leading-tight">
                    {{ $post->title }}
                </h1>

                @if ($post->excerpt)
                    <p class="mt-6 text-xl text-slate-600 leading-relaxed">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </header>

            @if ($post->featuredImageUrl())
                <div class="mb-10">
                    <img
                        src="{{ $post->featuredImageUrl() }}"
                        alt="{{ $post->title }}"
                        class="w-full rounded-2xl border border-slate-200 shadow-sm object-contain max-h-[28rem] bg-slate-50"
                    />
                </div>
            @endif

            <div class="text-slate-700 leading-relaxed space-y-4 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-slate-900 [&_h2]:font-display [&_h2]:mt-8 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-slate-900 [&_p]:text-base [&_a]:text-primary [&_a]:underline [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6">
                {!! $post->content !!}
            </div>

            @php
                $postActionButtonClass = 'inline-flex items-center gap-2 px-3 h-10 rounded-lg cursor-pointer border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 hover:border-primary dark:hover:border-blue-400 hover:text-primary dark:hover:text-blue-400 active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed';
                $postIconButtonClass = 'inline-flex items-center justify-center w-10 h-10 rounded-lg cursor-pointer border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 hover:border-primary dark:hover:border-blue-400 hover:text-primary dark:hover:text-blue-400 active:scale-95 transition-all';
            @endphp

            <div class="mt-8 flex items-center gap-2">
                @auth
                    <button
                        type="button"
                        data-post-like="{{ route('posts.like.toggle', $post) }}"
                        data-csrf="{{ csrf_token() }}"
                        aria-label="Like"
                        class="{{ $postActionButtonClass }}"
                    >
                        <i
                            data-like-icon
                            class="{{ $isLikedByUser ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }}"
                            @if ($isLikedByUser) style="color: rgb(255, 0, 0);" @endif
                        ></i>
                        <span data-like-count class="text-sm font-semibold tabular-nums">{{ $post->likes_count }}</span>
                    </button>
                @else
                    <a
                        href="{{ route('login') }}"
                        aria-label="Sign in to like"
                        class="{{ $postActionButtonClass }}"
                    >
                        <i class="fa-regular fa-heart"></i>
                        <span class="text-sm font-semibold tabular-nums">{{ $post->likes_count }}</span>
                    </a>
                @endauth

                <button
                    type="button"
                    data-copy-url="{{ url(route('posts.show', $post)) }}"
                    aria-label="Copy link"
                    class="{{ $postIconButtonClass }}"
                >
                    <i class="fa-solid fa-link" data-copy-icon></i>
                </button>

                @auth
                    <button
                        type="button"
                        data-post-bookmark="{{ route('posts.bookmark.toggle', $post) }}"
                        data-csrf="{{ csrf_token() }}"
                        aria-label="Save"
                        class="{{ $postIconButtonClass }}"
                    >
                        <i
                            data-bookmark-icon
                            class="{{ $isBookmarkedByUser ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark' }}"
                            @if ($isBookmarkedByUser) style="color: rgb(255, 212, 59);" @endif
                        ></i>
                    </button>
                @else
                    <a
                        href="{{ route('login') }}"
                        aria-label="Sign in to save"
                        class="{{ $postIconButtonClass }}"
                    >
                        <i class="fa-regular fa-bookmark"></i>
                    </a>
                @endauth
            </div>

            <section
                id="comments"
                class="mt-16 pt-12 border-t border-slate-200"
                data-reply-to="{{ session('reply_to') }}"
                data-reply-body="{{ session('reply_to') ? old('body') : '' }}"
            >
                <h2 class="text-2xl font-bold text-slate-900 font-display tracking-tight mb-8">
                    Comments
                    <span class="text-slate-400 font-normal text-lg">({{ $post->comments_count }})</span>
                </h2>

                @auth
                    <form
                        id="comment-composer-form"
                        method="POST"
                        action="{{ route('posts.comments.store', $post) }}"
                        class="mb-10"
                    >
                        @csrf

                        <label for="comment-body" class="block text-sm font-semibold text-slate-700 mb-2">
                            Add a comment
                        </label>
                        <textarea
                            id="comment-body"
                            name="body"
                            rows="4"
                            required
                            placeholder="Share your thoughts..."
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors @error('body') border-red-300 focus:border-red-400 focus:ring-red-200 @enderror"
                        >{{ session('reply_to') ? '' : old('body') }}</textarea>

                        @if (! session('reply_to'))
                            @error('body')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif

                        <button
                            type="submit"
                            id="comment-composer-submit"
                            class="mt-4 inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white hover:bg-blue-700 transition-colors disabled:opacity-60"
                        >
                            Post comment
                        </button>
                    </form>
                @else
                    <p class="mb-10 text-slate-600">
                        <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-blue-700 transition-colors">Sign in</a>
                        to join the conversation.
                    </p>
                @endauth

                @if ($post->comments_count === 0)
                    <p class="text-slate-500 text-sm">No comments yet. Be the first to share your thoughts.</p>
                @else
                    <ul class="space-y-6">
                        @foreach ($comments as $comment)
                            <x-comment
                                :post="$post"
                                :comment="$comment"
                                :comment-votes="$commentVotes"
                                :can-reply="true"
                            />
                        @endforeach
                    </ul>

                    @if ($comments->hasPages())
                        <div class="mt-10">
                            {{ $comments->fragment('comments')->links() }}
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </article>
@endsection
