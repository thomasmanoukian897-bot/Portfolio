@props([
    'notification',
    'followingIds',
])

@php
    $data = $notification->data;
    $actorId = $data['actor_id'] ?? null;
    $isFollowing = $actorId !== null && $followingIds->contains($actorId);
    $timestamp = \App\Http\Controllers\NotificationController::formatTimestamp($notification->created_at);
    $actionText = match ($data['type'] ?? null) {
        'follow' => 'started following you',
        'post_liked' => 'liked your post',
        'post_commented' => 'commented on your post:',
        'user_mentioned' => 'mentioned you in a comment:',
        'post_published' => 'published a new post',
        default => 'sent you a notification',
    };
    $postUrl = ! empty($data['post_slug'])
        ? route('posts.show', $data['post_slug']).(! empty($data['comment_id']) ? '#comment-'.$data['comment_id'] : '')
        : null;
@endphp

<div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
    <a href="{{ route('users.show', $actorId) }}" class="shrink-0">
        @if (! empty($data['actor_avatar_url']))
            <img
                src="{{ $data['actor_avatar_url'] }}"
                alt="{{ $data['actor_name'] ?? 'User' }}"
                class="w-11 h-11 rounded-full object-cover"
            />
        @else
            <div class="w-11 h-11 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-600 dark:text-slate-300">
                {{ strtoupper(substr($data['actor_name'] ?? '?', 0, 1)) }}
            </div>
        @endif
    </a>

    <div class="flex-1 min-w-0">
        <p class="text-sm leading-snug text-slate-600 dark:text-slate-400">
            <a href="{{ route('users.show', $actorId) }}" class="font-semibold text-slate-900 dark:text-slate-100 hover:underline">
                {{ $data['actor_handle'] ?? $data['actor_name'] ?? 'Someone' }}
            </a>
            <span>{{ ' '.$actionText }}</span>
            <span class="text-slate-400 dark:text-slate-500">{{ ' '.$timestamp }}</span>
        </p>

        @if (in_array($data['type'] ?? null, ['post_commented', 'user_mentioned'], true) && ! empty($data['comment_body']))
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                {{ $data['comment_body'] }}
            </p>
        @endif
    </div>

    <div class="shrink-0">
        @if (($data['type'] ?? null) === 'follow')
            <form method="POST" action="{{ route('users.follow.toggle', $actorId) }}">
                @csrf
                <input type="hidden" name="from_notifications" value="1">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $isFollowing ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600' : 'bg-primary hover:bg-primary/90 text-white' }}"
                >
                    {{ $isFollowing ? 'Following' : 'Follow Back' }}
                </button>
            </form>
        @elseif (in_array($data['type'] ?? null, ['post_liked', 'post_commented', 'user_mentioned', 'post_published'], true) && ! empty($data['post_image_url']))
            <a href="{{ $postUrl }}" class="block">
                <img
                    src="{{ $data['post_image_url'] }}"
                    alt="{{ $data['post_title'] ?? 'Post' }}"
                    class="w-11 h-11 rounded object-cover"
                />
            </a>
        @elseif (in_array($data['type'] ?? null, ['post_liked', 'post_commented', 'user_mentioned', 'post_published'], true))
            <a
                href="{{ $postUrl }}"
                class="inline-flex items-center justify-center w-11 h-11 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400"
            >
                <i class="fa-solid fa-file-lines text-sm" aria-hidden="true"></i>
            </a>
        @endif
    </div>
</div>
