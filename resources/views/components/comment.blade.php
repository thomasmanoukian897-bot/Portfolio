@props([
    'post',
    'comment',
    'commentVotes',
    'canReply' => false,
])

@php
    $userVote = $commentVotes->get($comment->id);
@endphp

<li id="comment-{{ $comment->id }}" class="flex gap-4">
    <a href="{{ route('users.show', $comment->user) }}" class="shrink-0 hover:opacity-80 transition-opacity">
        <x-user-avatar :user="$comment->user" size="sm" />
    </a>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a
                    href="{{ route('users.show', $comment->user) }}"
                    class="font-semibold text-slate-900 hover:text-primary transition-colors"
                >
                    {{ $comment->user->name }}
                </a>
                <span class="text-slate-300">&middot;</span>
                <time datetime="{{ $comment->created_at->toDateString() }}" class="text-slate-500">
                    {{ $comment->created_at->diffForHumans() }}
                </time>
            </div>

            @can('delete', $comment)
                <form
                    method="POST"
                    action="{{ route('posts.comments.destroy', [$post, $comment]) }}"
                    onsubmit="return confirm('Delete this comment?')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        aria-label="Delete comment"
                        class="inline-flex items-center justify-center px-2 py-1 rounded text-xs text-red-600 hover:bg-red-50 transition-colors"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            @endcan
        </div>

        <p class="text-slate-700 whitespace-pre-wrap">{{ $comment->body }}</p>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2" data-comment-vote-group>
                @auth
                    <button
                        type="button"
                        data-comment-vote="{{ route('posts.comments.vote', [$post, $comment]) }}"
                        data-vote-type="up"
                        data-csrf="{{ csrf_token() }}"
                        aria-label="Thumbs up"
                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-slate-600 hover:text-primary border border-transparent hover:border-slate-200 transition-colors disabled:opacity-60"
                    >
                        <i
                            data-vote-icon
                            class="{{ $userVote === 'up' ? 'fa-solid fa-thumbs-up' : 'fa-regular fa-thumbs-up' }}"
                            @if ($userVote === 'up') style="color: rgb(255, 212, 59);" @endif
                        ></i>
                        <span data-vote-count="up" class="text-xs font-semibold tabular-nums">{{ $comment->upvotes_count }}</span>
                    </button>

                    <button
                        type="button"
                        data-comment-vote="{{ route('posts.comments.vote', [$post, $comment]) }}"
                        data-vote-type="down"
                        data-csrf="{{ csrf_token() }}"
                        aria-label="Thumbs down"
                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-slate-600 hover:text-primary border border-transparent hover:border-slate-200 transition-colors disabled:opacity-60"
                    >
                        <i
                            data-vote-icon
                            class="{{ $userVote === 'down' ? 'fa-solid fa-thumbs-down' : 'fa-regular fa-thumbs-down' }}"
                            @if ($userVote === 'down') style="color: rgb(255, 0, 0);" @endif
                        ></i>
                        <span data-vote-count="down" class="text-xs font-semibold tabular-nums">{{ $comment->downvotes_count }}</span>
                    </button>
                @else
                    <a
                        href="{{ route('login') }}"
                        aria-label="Sign in to vote"
                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-slate-600 hover:text-primary transition-colors"
                    >
                        <i class="fa-regular fa-thumbs-up"></i>
                        <span class="text-xs font-semibold tabular-nums">{{ $comment->upvotes_count }}</span>
                    </a>

                    <a
                        href="{{ route('login') }}"
                        aria-label="Sign in to vote"
                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-slate-600 hover:text-primary transition-colors"
                    >
                        <i class="fa-regular fa-thumbs-down"></i>
                        <span class="text-xs font-semibold tabular-nums">{{ $comment->downvotes_count }}</span>
                    </a>
                @endauth
            </div>

            @if ($canReply)
                @auth
                    <button
                        type="button"
                        data-comment-reply="{{ $comment->id }}"
                        data-comment-reply-author="{{ $comment->user->name }}"
                        data-comment-reply-action="{{ route('posts.comments.reply', [$post, $comment]) }}"
                        data-comment-reply-csrf="{{ csrf_token() }}"
                        class="text-xs font-semibold text-slate-600 underline underline-offset-2 hover:text-slate-900 transition-colors"
                    >
                        Reply
                    </button>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="text-xs font-semibold text-slate-600 underline underline-offset-2 hover:text-slate-900 transition-colors"
                    >
                        Reply
                    </a>
                @endauth

                @if ($comment->replies->isNotEmpty())
                    @php
                        $repliesExpanded = session('show_replies_for') == $comment->id
                            || session('reply_to') == $comment->id;
                        $repliesCount = $comment->replies->count();
                    @endphp
                    <button
                        type="button"
                        data-comment-replies-toggle="{{ $comment->id }}"
                        data-replies-count="{{ $repliesCount }}"
                        aria-expanded="{{ $repliesExpanded ? 'true' : 'false' }}"
                        aria-controls="comment-replies-{{ $comment->id }}"
                        class="text-xs font-semibold text-slate-600 underline underline-offset-2 hover:text-slate-900 transition-colors"
                    >
                        @if ($repliesExpanded)
                            Hide replies
                        @else
                            {{ $repliesCount }} {{ Str::plural('reply', $repliesCount) }}
                        @endif
                    </button>
                @endif
            @endif
        </div>

        <div
            data-comment-reply-slot="{{ $comment->id }}"
            @class(['mt-4 ml-2 border-l-2 border-slate-200 pl-4', 'hidden' => session('reply_to') != $comment->id])
        ></div>

        @if ($canReply && $comment->replies->isNotEmpty())
            @php
                $repliesExpanded = session('show_replies_for') == $comment->id
                    || session('reply_to') == $comment->id;
            @endphp
            <ul
                id="comment-replies-{{ $comment->id }}"
                data-comment-replies-list="{{ $comment->id }}"
                @class([
                    'mt-6 space-y-6 border-l-2 border-slate-100 pl-4',
                    'hidden' => ! $repliesExpanded,
                ])
            >
                @foreach ($comment->replies as $reply)
                    <x-comment
                        :post="$post"
                        :comment="$reply"
                        :comment-votes="$commentVotes"
                    />
                @endforeach
            </ul>
        @endif
    </div>
</li>
