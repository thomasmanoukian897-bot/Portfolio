@props([
    'conversation',
    'active' => false,
])

@php
    $viewer = auth()->user();
    $latestMessage = $conversation->latestMessage;
    $hasUnread = $conversation->hasUnreadMessagesFor($viewer);
@endphp

<a
    href="{{ route('messages.show', $conversation) }}"
    @class([
        'flex items-center gap-3 px-4 py-3 transition-colors border-b border-slate-100 dark:border-slate-800/80',
        'bg-primary/5 dark:bg-primary/10' => $active,
        'hover:bg-slate-50 dark:hover:bg-slate-800/50' => ! $active,
    ])
>
  @if ($conversation->isDirect() && $conversation->otherParticipant($viewer))
        <x-user-avatar :user="$conversation->otherParticipant($viewer)" size="sm" />
    @else
        <x-conversation-avatar :conversation="$conversation" size="sm" />
    @endif

    <div class="min-w-0 flex-1">
        <div class="flex items-center justify-between gap-2">
            <p @class([
                'text-sm truncate',
                'font-semibold text-slate-900 dark:text-slate-100' => $hasUnread,
                'font-medium text-slate-800 dark:text-slate-200' => ! $hasUnread,
            ])>
                {{ $conversation->displayNameFor($viewer) }}
            </p>
            @if ($latestMessage?->created_at)
                <span class="text-xs text-slate-400 shrink-0">
                    {{ $latestMessage->created_at->isToday() ? $latestMessage->created_at->format('g:i A') : $latestMessage->created_at->format('M j') }}
                </span>
            @endif
        </div>
        @if ($latestMessage)
            <p @class([
                'text-xs truncate mt-0.5',
                'font-medium text-slate-700 dark:text-slate-300' => $hasUnread,
                'text-slate-500 dark:text-slate-400' => ! $hasUnread,
            ])>
                @if ($conversation->isGroup() && $latestMessage->user_id !== $viewer->id)
                    <span class="font-medium">{{ $latestMessage->user->name }}:</span>
                @endif
                {{ str($latestMessage->body)->limit(60) }}
            </p>
        @else
            <p class="text-xs text-slate-400 mt-0.5">No messages yet</p>
        @endif
    </div>

    @if ($hasUnread)
        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
    @endif
</a>
